<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Кафедраи технологияҳои иттилоотӣ ва фанҳои дақиқ',
                'code' => 'KIT',
                'description' => 'Технологияҳои иттилоотӣ ва фанҳои дақиқ',
                'is_active' => true,
            ],
            [
                'name' => 'Кафедраи технологиҳои саноатӣ ва фанҳои табиӣ',
                'code' => 'KSN',
                'description' => 'Технологиҳои саноатӣ ва фанҳои табиӣ',
                'is_active' => true,
            ],
            [
                'name' => 'Кафедраи забонҳо, педагогикаи касбӣ ва фанҳои ҷамъиятӣ',
                'code' => 'KZP',
                'description' => 'Забонҳо, педагогикаи касбӣ ва фанҳои ҷамъиятӣ',
                'is_active' => true,
            ],
            [
                'name' => 'Кафедраи иқтисодиёт ва менеҷмент',
                'code' => 'KEM',
                'description' => 'Иқтисодиёт ва менеҷмент',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['code' => $department['code']],
                $department
            );
        }
    }
}
