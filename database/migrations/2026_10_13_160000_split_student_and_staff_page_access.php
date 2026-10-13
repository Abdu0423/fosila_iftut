<?php

use App\Models\AccessPage;
use App\Models\Role;
use App\Support\PageAccess;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! PageAccess::tablesReady()) {
            return;
        }

        foreach (PageAccess::definitions() as $index => $page) {
            AccessPage::updateOrCreate(
                ['key' => $page['key']],
                ['title' => $page['title'], 'sort' => $index]
            );
        }

        $pages = AccessPage::all()->keyBy('key');
        $studentMap = [
            'dashboard' => 'student_dashboard',
            'tests' => 'student_tests',
            'ssr' => 'student_ssr',
            'coursework' => 'student_coursework',
            'grades' => 'student_grades',
            'chat' => 'student_chat',
            'assignments' => 'student_assignments',
            'library' => 'student_library',
        ];

        $studentRole = Role::where('name', 'student')->first();
        if ($studentRole) {
            $detach = [];
            foreach ($studentMap as $from => $to) {
                if (isset($pages[$from])) {
                    $detach[] = $pages[$from]->id;
                }
            }
            if ($detach) {
                $studentRole->accessPages()->detach($detach);
            }

            $attach = [];
            foreach ($studentMap as $to) {
                if (isset($pages[$to])) {
                    $attach[] = $pages[$to]->id;
                }
            }
            if (isset($pages['student_schedule'])) {
                $attach[] = $pages['student_schedule']->id;
            }
            $studentRole->accessPages()->syncWithoutDetaching($attach);

            $staffIds = $pages
                ->filter(fn ($page) => in_array($page->key, PageAccess::keysForAudience('staff'), true))
                ->pluck('id')
                ->all();
            $studentRole->accessPages()->detach($staffIds);
        }

        $studentIds = $pages
            ->filter(fn ($page) => in_array($page->key, PageAccess::keysForAudience('student'), true))
            ->pluck('id')
            ->all();

        foreach (PageAccess::defaultGrants() as $roleName => $keys) {
            if ($roleName === 'student') {
                continue;
            }

            $role = Role::where('name', $roleName)->first();
            if (! $role) {
                continue;
            }

            $role->accessPages()->detach($studentIds);

            $ids = collect($keys)
                ->map(fn ($key) => $pages[$key]->id ?? null)
                ->filter()
                ->all();
            $role->accessPages()->syncWithoutDetaching($ids);
        }
    }

    public function down(): void
    {
    }
};
