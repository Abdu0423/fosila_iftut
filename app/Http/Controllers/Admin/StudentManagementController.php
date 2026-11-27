<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use App\Support\PhoneNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            $search = trim((string) $request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('dad_phone', 'like', "%{$search}%")
                  ->orWhere('mom_phone', 'like', "%{$search}%");
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

        return spa('Admin/Students/Management/Index', [
            'students' => $students,
            'groups' => $this->groupOptions(),
            'filters' => $request->only(['search', 'group_id']),
        ]);
    }

    private function groupOptions(bool $withDisplayName = false)
    {
        return Group::orderBy('name')
            ->get()
            ->map(function ($group) use ($withDisplayName) {
                $item = [
                    'id' => $group->id,
                    'name' => $group->name,
                ];
                if ($withDisplayName) {
                    $item['display_name'] = $group->full_name ?? $group->name;
                }

                return $item;
            });
    }

    /**
     * Быстрое добавление студента с автоматической ролью student
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'last_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'group_id' => 'nullable|integer|exists:groups,id',
            'phone' => 'nullable|string|max:255',
            'dad_phone' => 'nullable|string|max:255',
            'mom_phone' => 'nullable|string|max:255',
        ], [
            'last_name.required' => 'Необходимо указать фамилию',
            'name.required' => 'Необходимо указать имя',
        ]);

        $phone = $this->normalizeOrFail($validated['phone'] ?? null, 'phone');
        $dadPhone = $this->normalizeOrFail($validated['dad_phone'] ?? null, 'dad_phone');
        $momPhone = $this->normalizeOrFail($validated['mom_phone'] ?? null, 'mom_phone');

        $studentRole = Role::firstOrCreate(
            ['name' => 'student'],
            ['description' => 'Студент']
        );

        $student = User::create([
            'last_name' => trim($validated['last_name']),
            'name' => trim($validated['name']),
            'middle_name' => isset($validated['middle_name']) && trim($validated['middle_name']) !== ''
                ? trim($validated['middle_name'])
                : null,
            'password' => bcrypt(Str::random(12)),
            'role_id' => $studentRole->id,
            'group_id' => $validated['group_id'] ?? null,
            'phone' => $phone,
            'dad_phone' => $dadPhone,
            'mom_phone' => $momPhone,
            'email_verified_at' => now(),
            'must_change_password' => true,
        ]);

        $student->load(['role', 'group']);

        if ($request->wantsJson() || $request->header('X-Spa') === '1') {
            return response()->json([
                'message' => 'Студент успешно добавлен',
                'student' => $student,
            ], 201);
        }

        return back()->with('success', 'Студент успешно добавлен');
    }

    private function normalizeOrFail(?string $raw, string $field): ?string
    {
        $normalized = PhoneNormalizer::normalize($raw);
        if (PhoneNormalizer::hasDigits($raw) && $normalized === null) {
            throw ValidationException::withMessages([
                $field => 'Введите корректный номер телефона (9 цифр после +992)',
            ]);
        }

        return $normalized;
    }

    /**
     * Переводы
     */
    public function transfers(Request $request)
    {
        return spa('Admin/Students/Transfers', [
            'groups' => $this->groupOptions(),
            'filters' => $request->only(['search', 'group_id']),
        ]);
    }

    /**
     * Форма создания перевода
     */
    public function createTransfer(Request $request)
    {
        $groups = $this->groupOptions(true);

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

        return spa('Admin/Students/Transfers/Create', [
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

