<?php

use App\Models\AccessPage;
use App\Models\Role;
use App\Support\PageAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_pages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('role_page', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('access_page_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'access_page_id']);
        });

        Role::firstOrCreate(['name' => 'department'], ['description' => 'Кафедра']);
        Role::firstOrCreate(['name' => 'faculty'], ['description' => 'Факультет']);

        foreach (PageAccess::definitions() as $index => $page) {
            AccessPage::updateOrCreate(
                ['key' => $page['key']],
                ['title' => $page['title'], 'sort' => $index]
            );
        }

        $pages = AccessPage::all()->keyBy('key');

        foreach (PageAccess::defaultGrants() as $roleName => $keys) {
            $role = Role::where('name', $roleName)->first();
            if (! $role) {
                continue;
            }

            $ids = collect($keys)
                ->map(fn ($key) => $pages[$key]->id ?? null)
                ->filter()
                ->all();

            $role->accessPages()->syncWithoutDetaching($ids);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_page');
        Schema::dropIfExists('access_pages');
        Role::whereIn('name', ['department', 'faculty'])->delete();
    }
};
