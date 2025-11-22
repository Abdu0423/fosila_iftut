<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'admin',
            'description' => 'Администратор системы'
        ]);

        Role::create([
            'name' => 'teacher',
            'description' => 'Учитель'
        ]);

        Role::create([
            'name' => 'student',
            'description' => 'Студент'
        ]);

        Role::create([
            'name' => 'education_department',
            'description' => 'Отдел образования'
        ]);
    }
}
