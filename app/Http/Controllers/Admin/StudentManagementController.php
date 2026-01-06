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
}

