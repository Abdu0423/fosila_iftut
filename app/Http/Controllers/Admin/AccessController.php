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
        $grants = $request->input('grants');
        if (! is_array($grants)) {
            $grants = [[
                'role_id' => $request->input('role_id'),
                'keys' => $request->input('keys', []),
            ]];
        }

        $validated = validator(['grants' => $grants], [
            'grants' => 'required|array|min:1',
            'grants.*.role_id' => 'required|integer|exists:roles,id',
            'grants.*.keys' => 'array',
            'grants.*.keys.*' => 'string',
        ])->validate();

        foreach (PageAccess::definitions() as $index => $page) {
            AccessPage::updateOrCreate(
                ['key' => $page['key']],
                ['title' => $page['title'], 'sort' => $index]
            );
        }

        foreach ($validated['grants'] as $grant) {
            $this->syncRole((int) $grant['role_id'], $grant['keys'] ?? []);
        }

        return back()->with('success', 'Доступ сохранён');
    }

    private function syncRole(int $roleId, array $keys): void
    {
        $role = Role::query()
            ->where('name', '!=', 'admin')
            ->findOrFail($roleId);

        $audience = $role->name === 'student' ? 'student' : 'staff';
        $allowedKeys = collect(PageAccess::definitions())
            ->filter(fn ($page) => ($page['audience'] ?? 'staff') === $audience)
            ->pluck('key');

        $ids = AccessPage::query()
            ->whereIn('key', collect($keys)->intersect($allowedKeys))
            ->pluck('id');

        $role->accessPages()->sync($ids);
    }
}
