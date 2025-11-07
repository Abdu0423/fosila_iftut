<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Events\MessageSent;
use App\Events\MessageRead;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Показать список чатов
     */
    public function index()
    {
        $user = auth()->user();
        
        // Загружаем роль пользователя
        $user->load('role');
        
        // Получаем чаты пользователя с последними сообщениями
        $chats = $user->chats()
            ->with(['lastMessage.user', 'users' => function($query) use ($user) {
                $query->where('user_id', '!=', $user->id);
            }])
            ->wherePivot('is_active', true)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($chat) use ($user) {
                // Добавляем информацию о пользователях чата с полными именами
                $chat->users = $chat->users->map(function ($chatUser) {
                    return [
                        'id' => $chatUser->id,
                        'name' => $chatUser->name,
                        'last_name' => $chatUser->last_name,
                        'email' => $chatUser->email,
                    ];
                });
                return $chat;
            });

        // Получаем всех пользователей для создания новых чатов
        $users = User::where('id', '!=', $user->id)
            ->where('role_id', '!=', null) // Только пользователи с ролями
            ->orderBy('name')
            ->get(['id', 'name', 'last_name', 'email']);

        // Подсчитываем общее количество непрочитанных сообщений
        $totalUnreadCount = 0;
        foreach ($chats as $chat) {
            $totalUnreadCount += $chat->unread_count ?? 0;
        }

        return Inertia::render('Chat/Index', [
            'chats' => $chats,
            'users' => $users,
            'unreadChatsCount' => $totalUnreadCount,
        ]);
    }

    /**
     * Показать конкретный чат
     */
    public function show(Chat $chat)
    {
        $user = auth()->user();
        
        // Проверяем, что пользователь участвует в чате
        if (!$chat->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'У вас нет доступа к этому чату');
        }

        // Загружаем сообщения чата
        $messages = $chat->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        // Отмечаем сообщения как прочитанные
        $chat->users()->updateExistingPivot($user->id, [
            'last_read_at' => now()
        ]);

        // Загружаем участников чата
        $chat->load(['users' => function($query) use ($user) {
            $query->where('user_id', '!=', $user->id);
        }]);

        return Inertia::render('Chat/Show', [
            'chat' => $chat,
            'messages' => $messages,
        ]);
    }

    /**
     * Создать новый чат
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'in:private,group',
            'name' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        // Загружаем роль пользователя
        $user->load('role');
        $otherUserId = $request->user_id;

        // Проверяем, не существует ли уже приватный чат между этими пользователями
        if ($request->type === 'private') {
            $existingChat = Chat::where('type', 'private')
                ->whereHas('users', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereHas('users', function($query) use ($otherUserId) {
                    $query->where('user_id', $otherUserId);
                })
                ->first();

            if ($existingChat) {
                // Определяем правильный роут в зависимости от роли пользователя
                $role = $user->role ? $user->role->name : 'student';
                $routeName = $role === 'admin' ? 'admin.chat.show' : 
                           ($role === 'teacher' ? 'teacher.chat.show' : 'student.chat.show');
                return redirect()->route($routeName, $existingChat);
            }
        }

        DB::beginTransaction();
        try {
            // Создаем чат
            $chat = Chat::create([
                'type' => $request->type ?? 'private',
                'name' => $request->name,
            ]);

            // Добавляем пользователей в чат
            $chat->users()->attach([
                $user->id => ['role' => 'admin', 'joined_at' => now()],
                $otherUserId => ['role' => 'member', 'joined_at' => now()]
            ]);

            DB::commit();

            // Возвращаемся на страницу индекса с обновленным списком чатов
            $role = $user->role ? $user->role->name : 'student';
            $routeName = $role === 'admin' ? 'admin.chat.index' : 
                       ($role === 'teacher' ? 'teacher.chat.index' : 'student.chat.index');
            return redirect()->route($routeName)->with('success', 'Чат успешно создан');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Ошибка при создании чата: ' . $e->getMessage());
        }
    }

    /**
     * Отправить сообщение
     */
    public function sendMessage(Request $request, Chat $chat)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'message_type' => 'in:text,image,file',
        ]);

        $user = auth()->user();
        
        // Проверяем, что пользователь участвует в чате
        if (!$chat->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'У вас нет доступа к этому чату');
        }

        $message = $chat->messages()->create([
            'user_id' => $user->id,
            'message' => $request->message,
            'message_type' => $request->message_type ?? 'text',
            'metadata' => $request->metadata,
        ]);

        // Загружаем пользователя для сообщения
        $message->load('user');

        // Отправляем событие о новом сообщении
        broadcast(new MessageSent($message));

        // Обновляем время последнего сообщения в чате
        $chat->touch();

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Получить сообщения чата
     */
    public function getMessages(Chat $chat, Request $request)
    {
        $user = auth()->user();
        
        // Проверяем, что пользователь участвует в чате
        if (!$chat->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'У вас нет доступа к этому чату');
        }

        $messages = $chat->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return response()->json($messages);
    }

    /**
     * Отметить сообщения как прочитанные
     */
    public function markAsRead(Chat $chat)
    {
        $user = auth()->user();
        
        // Проверяем, что пользователь участвует в чате
        if (!$chat->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'У вас нет доступа к этому чату');
        }

        $readAt = now();
        
        // Обновляем время последнего прочтения
        $chat->users()->updateExistingPivot($user->id, [
            'last_read_at' => $readAt
        ]);

        // Отправляем событие о прочтении сообщений
        broadcast(new MessageRead($user->id, $chat->id, $readAt));

        return response()->json(['success' => true]);
    }

    /**
     * Покинуть чат
     */
    public function leave(Chat $chat)
    {
        $user = auth()->user();
        
        // Проверяем, что пользователь участвует в чате
        if (!$chat->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'У вас нет доступа к этому чату');
        }

        // Удаляем пользователя из чата
        $chat->users()->detach($user->id);

        // Если это приватный чат и в нем остался только один пользователь, удаляем чат
        if ($chat->type === 'private' && $chat->users()->count() <= 1) {
            $chat->delete();
            return redirect()->route('chat.index')->with('success', 'Чат удален');
        }

        return redirect()->route('chat.index')->with('success', 'Вы покинули чат');
    }
}