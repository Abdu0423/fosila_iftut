<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentManagementController extends Controller
{
    /**
     * Управление студентами
     */
    public function index(Request $request)
    {
        $query = User::with(['role', 'group'])
            ->whereHas('role', function($q) {
                $q->where('name', 'student');
            });

        // Поиск
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Фильтр по группе
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        $students = $query->orderBy('last_name')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $groups = Group::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                ];
            });

        return Inertia::render('Admin/Students/Management/Index', [
            'students' => $students,
            'groups' => $groups,
            'filters' => $request->only(['search', 'group_id']),
        ]);
    }

    /**
     * Переводы
     */
    public function transfers(Request $request)
    {
        $groups = Group::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                ];
            });

        return Inertia::render('Admin/Students/Transfers', [
            'groups' => $groups,
            'filters' => $request->only(['search', 'group_id']),
        ]);
    }

    /**
     * Форма создания перевода
     */
    public function createTransfer(Request $request)
    {
        $groups = Group::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'display_name' => $group->full_name ?? $group->name,
                ];
            });

        $students = User::whereHas('role', function($q) {
            $q->where('name', 'student');
        })
        ->with('group')
        ->orderBy('last_name')
        ->orderBy('name')
        ->get()
        ->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'last_name' => $student->last_name,
                'middle_name' => $student->middle_name,
                'group_id' => $student->group_id,
                'group_name' => $student->group ? $student->group->name : null,
            ];
        });

        return Inertia::render('Admin/Students/Transfers/Create', [
            'groups' => $groups,
            'students' => $students,
        ]);
    }

    /**
     * Сохранить перевод
     */
    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'from_group_id' => 'required|exists:groups,id',
            'to_group_id' => 'required|exists:groups,id|different:from_group_id',
            'transfer_date' => 'nullable|date',
            'reason' => 'nullable|string|max:1000',
        ]);

        // Получаем студента
        $student = User::findOrFail($validated['student_id']);

        // Проверяем, что студент действительно в указанной группе
        if ($student->group_id != $validated['from_group_id']) {
            return back()->withErrors([
                'from_group_id' => 'Студент не находится в указанной группе'
            ])->withInput();
        }

        // Обновляем группу студента
        $student->update([
            'group_id' => $validated['to_group_id']
        ]);

        // TODO: Здесь можно добавить логирование перевода в отдельную таблицу transfers
        // Transfer::create([
        //     'student_id' => $validated['student_id'],
        //     'from_group_id' => $validated['from_group_id'],
        //     'to_group_id' => $validated['to_group_id'],
        //     'transfer_date' => $validated['transfer_date'] ?? now(),
        //     'reason' => $validated['reason'],
        //     'created_by' => auth()->id(),
        // ]);

        return redirect()->route('admin.students.transfers')
            ->with('success', 'Студент успешно переведен в другую группу');
    }
}

