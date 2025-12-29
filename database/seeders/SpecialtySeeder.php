<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Specialty;
use App\Models\Department;
use Illuminate\Support\Facades\File;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sqlFile = database_path('specialities.sql');
        
        if (!File::exists($sqlFile)) {
            $this->command->warn("Файл {$sqlFile} не найден. Используются данные по умолчанию.");
            $this->seedDefaultData();
            return;
        }

        $content = File::get($sqlFile);
        
        // Парсим INSERT запросы
        preg_match_all(
            "/insert\s+into\s+`ikhtisos`\s*\([^)]+\)\s+values\s*\(([^)]+)\)/i",
            $content,
            $matches
        );

        if (empty($matches[1])) {
            $this->command->warn("Не найдено данных в SQL файле. Используются данные по умолчанию.");
            $this->seedDefaultData();
            return;
        }

        $this->command->info("Найдено " . count($matches[1]) . " записей для импорта.");

        foreach ($matches[1] as $valuesString) {
            // Парсим значения из строки
            $values = $this->parseValues($valuesString);
            
            if (count($values) < 7) {
                continue;
            }

            $oldId = (int)trim($values[0], "'\"");
            $name = trim($values[1], "'\"");
            $shortName = !empty($values[2]) && strtoupper(trim($values[2])) !== 'NULL' ? trim($values[2], "'\"") : null;
            $code = !empty($values[3]) && strtoupper(trim($values[3])) !== 'NULL' ? trim($values[3], "'\"") : null;
            $idKafedra = !empty($values[4]) && strtoupper(trim($values[4])) !== 'NULL' ? (int)trim($values[4]) : null;
            $status = !empty($values[5]) ? (int)trim($values[5]) : 2;
            // $idFakultet игнорируем, так как нет faculty_id в новой структуре

            // Проверяем, существует ли уже запись с таким old_id
            $existing = Specialty::where('old_id', $oldId)->first();
            if ($existing) {
                $this->command->line("Специальность с old_id={$oldId} уже существует, пропускаем.");
                continue;
            }

            // Обрабатываем пустой или невалидный code
            if (empty($code) || $code === '00000' || $code === 'NULL') {
                // Генерируем уникальный code на основе old_id
                $code = 'SPEC-' . $oldId;
            }

            // Проверяем, существует ли уже запись с таким code
            $existingByCode = Specialty::where('code', $code)->first();
            if ($existingByCode) {
                // Если code уже существует, генерируем уникальный
                $code = 'SPEC-' . $oldId . '-' . time();
            }

            // Находим department по id (если есть)
            $departmentId = null;
            if ($idKafedra) {
                $department = Department::where('id', $idKafedra)->first();
                $departmentId = $department ? $department->id : null;
            }

            try {
                Specialty::create([
                    'old_id' => $oldId,
                    'name' => $name,
                    'short_name' => $shortName,
                    'code' => $code,
                    'department_id' => $departmentId,
                    'is_active' => $status == 1,
                ]);
            } catch (\Exception $e) {
                $this->command->warn("Ошибка при создании специальности с old_id={$oldId}: " . $e->getMessage());
                continue;
            }
        }

        $this->command->info("Импорт специальностей завершен.");
    }

    /**
     * Парсит значения из строки INSERT
     */
    private function parseValues($valuesString)
    {
        $values = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = null;
        
        for ($i = 0; $i < strlen($valuesString); $i++) {
            $char = $valuesString[$i];
            
            if (($char === '"' || $char === "'") && ($i === 0 || $valuesString[$i - 1] !== '\\')) {
                if (!$inQuotes) {
                    $inQuotes = true;
                    $quoteChar = $char;
                } elseif ($char === $quoteChar) {
                    $inQuotes = false;
                    $quoteChar = null;
                }
                $current .= $char;
            } elseif ($char === ',' && !$inQuotes) {
                $values[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }
        
        if (!empty($current)) {
            $values[] = trim($current);
        }
        
        return $values;
    }

    /**
     * Данные по умолчанию, если SQL файл не найден
     */
    private function seedDefaultData()
    {
        $specialties = [
            [
                'name' => 'Информационные системы и технологии',
                'code' => '09.03.02',
                'description' => 'Бакалавриат по направлению информационные системы и технологии',
                'duration_years' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Прикладная информатика',
                'code' => '09.03.03',
                'description' => 'Бакалавриат по направлению прикладная информатика',
                'duration_years' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($specialties as $specialty) {
            Specialty::create($specialty);
        }
    }
}
