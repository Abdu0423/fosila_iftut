<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Schedule;
use App\Models\User;
use App\Events\MessageSent;
use App\Events\MessageRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    protected function chatRoute(string $action): string
    {
        $path = trim(request()->path(), '/');
        if (str_starts_with($path, 'api/')) {
            $path = substr($path, 4);
        }

        foreach (['admin', 'teacher', 'education', 'registration', 'department', 'faculty', 'student'] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $prefix.'.chat.'.$action;
            }
        }

        return 'student.chat.'.$action;
    }

    protected function messageableUsers(User $user)
    {
        $user->loadMissing('role');

        $query = User::query()
            ->where('id', '!=', $user->id)
            ->whereNotNull('role_id');

        if ($user->isAdmin()) {
            return $query->orderBy('last_name')->orderBy('name')->orderBy('middle_name');
        }

        $ids = collect();

        if ($user->isStudent()) {
            if ($user->group_id) {
                $ids = $ids->merge(
                    Schedule::where('group_id', $user->group_id)->pluck('teacher_id')
                );

                $departmentId = Group::where('id', $user->group_id)->value('department_id');
                if ($departmentId) {
                    $department = Department::with('faculty')->find($departmentId);
                    if ($department?->user_id) {
                        $ids->push($department->user_id);
                    }
                    if ($department?->faculty?->user_id) {
                        $ids->push($department->faculty->user_id);
                    }
                }
            }
        } else {
            $ids = $ids->merge(
                User::query()
                    ->where('id', '!=', $user->id)
                    ->whereNotNull('role_id')
                    ->whereHas('role', fn ($role) => $role->where('name', '!=', 'student'))
                    ->pluck('id')
            );

            $groupIds = collect();
            if ($user->isTeacher()) {
                $groupIds = $groupIds->merge(
                    Schedule::where('teacher_id', $user->id)->pluck('group_id')
                );
            }
            if ($user->isDepartment()) {
                $department = $user->ownedDepartment();
                if ($department) {
                    $groupIds = $groupIds->merge(
                        Group::where('department_id', $department->id)->pluck('id')
                    );
                }
            }
            if ($user->isFaculty()) {
                $faculty = Faculty::where('user_id', $user->id)->first();
                if ($faculty) {
                    $departmentIds = $faculty->departments()->pluck('id');
                    $groupIds = $groupIds->merge(
                        Group::whereIn('department_id', $departmentIds)->pluck('id')
                    );
                }
            }

            $groupIds = $groupIds->filter()->unique()->values();
            if ($groupIds->isNotEmpty()) {
                $ids = $ids->merge(
                    User::query()
                        ->whereIn('group_id', $groupIds)
                        ->whereHas('role', fn ($role) => $role->where('name', 'student'))
                        ->pluck('id')
                );
            }
        }

        $ids = $ids
            ->filter()
            ->unique()
            ->reject(fn ($id) => (int) $id === (int) $user->id)
            ->values();

        return User::query()
            ->whereIn('id', $ids)
            ->whereNotNull('role_id')
            ->orderBy('last_name')
            ->orderBy('name')
            ->orderBy('middle_name');
    }

    protected function chatListItem(Chat $chat, User $user): array
    {
        $chat->load(['lastMessage.user']);
        $partners = $chat->users()->where('user_id', '!=', $user->id)->get();
        $partner = $partners->first();

        return [
            'id' => $chat->id,
            'type' => $chat->type,
            'name' => $chat->name,
            'display_name' => $partner?->full_name ?: ($chat->name ?: 'Чат'),
            'display_avatar' => null,
            'unread_count' => 0,
            'last_message' => $chat->lastMessage,
            'users' => $partners->map(fn (User $chatUser) => [
                'id' => $chatUser->id,
                'name' => $chatUser->name,
                'last_name' => $chatUser->last_name,
                'middle_name' => $chatUser->middle_name,
                'email' => $chatUser->email,
            ])->values(),
        ];
    }

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
                        'middle_name' => $chatUser->middle_name,
                        'email' => $chatUser->email,
                    ];
                });
                return $chat;
            });

        $users = $this->messageableUsers($user)->get(['id', 'name', 'last_name', 'middle_name', 'email']);

        // Подсчитываем общее количество непрочитанных сообщений
        $totalUnreadCount = 0;
        foreach ($chats as $chat) {
            $totalUnreadCount += $chat->unread_count ?? 0;
        }

        return spa('Chat/Index', [
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

        return spa('Chat/Show', [
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
        $type = $request->input('type', 'private');

        if (! $this->messageableUsers($user)->where('id', $otherUserId)->exists()) {
            abort(403, 'Нельзя написать этому пользователю');
        }

        // Проверяем, не существует ли уже приватный чат между этими пользователями
        if ($type === 'private') {
            $existingChat = Chat::where('type', 'private')
                ->whereHas('users', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereHas('users', function($query) use ($otherUserId) {
                    $query->where('user_id', $otherUserId);
                })
                ->first();

            if ($existingChat) {
                if ($request->expectsJson()) {
                    return response()->json(['chat' => $this->chatListItem($existingChat, $user)]);
                }

                return redirect()->route($this->chatRoute('show'), $existingChat);
            }
        }

        DB::beginTransaction();
        try {
            // Создаем чат
            $chat = Chat::create([
                'type' => $type,
                'name' => $request->name,
            ]);

            // Добавляем пользователей в чат
            $chat->users()->attach([
                $user->id => ['role' => 'admin', 'joined_at' => now()],
                $otherUserId => ['role' => 'member', 'joined_at' => now()]
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['chat' => $this->chatListItem($chat, $user)]);
            }

            return redirect()->route($this->chatRoute('index'))->with('success', 'Чат успешно создан');
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
            return redirect()->route($this->chatRoute('index'))->with('success', 'Чат удален');
        }

        return redirect()->route($this->chatRoute('index'))->with('success', 'Вы покинули чат');
    }
}