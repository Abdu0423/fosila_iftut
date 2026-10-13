<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessPage;
use App\Models\Role;
use App\Support\PageAccess;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->where('name', '!=', 'admin')
            ->orderBy('id')
            ->get(['id', 'name', 'description']);

        $pages = [];
        foreach ($roles as $role) {
            $pages[$role->id] = PageAccess::pagesForRole($role);
        }

        return spa('Admin/Access/Index', [
            'roles' => $roles,
            'pagesByRole' => $pages,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'keys' => 'array',
            'keys.*' => 'string',
        ]);

        $role = Role::query()
            ->where('name', '!=', 'admin')
            ->findOrFail($validated['role_id']);

        $audience = $role->name === 'student' ? 'student' : 'staff';
        $allowedKeys = collect(PageAccess::definitions())
            ->filter(fn ($page) => ($page['audience'] ?? 'staff') === $audience)
            ->pluck('key');

        $keys = collect($validated['keys'] ?? [])->intersect($allowedKeys);

        foreach (PageAccess::definitions() as $index => $page) {
            AccessPage::updateOrCreate(
                ['key' => $page['key']],
                ['title' => $page['title'], 'sort' => $index]
            );
        }

        $ids = AccessPage::query()->whereIn('key', $keys)->pluck('id');
        $role->accessPages()->sync($ids);

        return back()->with('success', 'Доступ сохранён');
    }
}
