<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function index()
    {
        $faculties = Faculty::orderBy('name')
            ->paginate(10);

        return spa('Admin/Faculties/Index', [
            'faculties' => $faculties,
            'filters' => [],
        ]);
    }

    /**
     * Обработка фильтров через POST запрос
     */
    public function filter(Request $request)
    {
        $faculties = Faculty::when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->when($request->filled('sortBy'), function ($query) use ($request) {
                $sortBy = $request->get('sortBy');
                $sortDesc = $request->boolean('sortDesc', false);
                $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
            }, function ($query) {
                $query->orderBy('name');
            })
            ->paginate($request->get('per_page', 10));

        return spa('Admin/Faculties/Index', [
            'faculties' => $faculties,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return spa('Admin/Faculties/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:faculties',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        Faculty::create($validated);

        return redirect()->route('admin.faculties.index')
            ->with('success', 'Факультет успешно создан');
    }

    public function show(Faculty $faculty)
    {
        $faculty->load(['departments']);

        return spa('Admin/Faculties/Show', [
            'faculty' => $faculty,
        ]);
    }

    public function edit(Faculty $faculty)
    {
        return spa('Admin/Faculties/Edit', [
            'faculty' => $faculty,
        ]);
    }

    public function update(Request $request, Faculty $faculty)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:faculties,name,' . $faculty->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $faculty->update($validated);

        return redirect()->route('admin.faculties.index')
            ->with('success', 'Факультет успешно обновлен');
    }

    public function destroy(Faculty $faculty)
    {
        $faculty->delete();

        return redirect()->route('admin.faculties.index')
            ->with('success', 'Факультет успешно удален');
    }
}
