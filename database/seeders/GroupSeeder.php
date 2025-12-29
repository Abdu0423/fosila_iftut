<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\Department;
use App\Models\Specialty;
use Illuminate\Support\Facades\File;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sqlFile = database_path('groups.sql');
        
        if (!File::exists($sqlFile)) {
            $this->command->warn("Файл {$sqlFile} не найден. Используются данные по умолчанию.");
            $this->seedDefaultData();
            return;
        }

        $content = File::get($sqlFile);
        
        // Парсим INSERT запросы
        preg_match_all(
            "/insert\s+into\s+`guruh`\s*\([^)]+\)\s+values\s*\(([^)]+)\)/i",
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
            
            if (count($values) < 9) {
                continue;
            }

            $oldId = (int)trim($values[0], "'\"");
            $name = trim($values[1], "'\"");
            $course = !empty($values[2]) && strtoupper(trim($values[2])) !== 'NULL' ? (int)trim($values[2]) : null;
            $idIkhtisos = !empty($values[3]) && strtoupper(trim($values[3])) !== 'NULL' && trim($values[3]) !== '0' ? (int)trim($values[3]) : null;
            $idKafedra = !empty($values[4]) && strtoupper(trim($values[4])) !== 'NULL' && trim($values[4]) !== '0' ? (int)trim($values[4]) : null;
            $soliKhonish = !empty($values[5]) && strtoupper(trim($values[5])) !== 'NULL' ? (int)trim($values[5]) : null;
            $soliDokhilshavi = !empty($values[6]) && strtoupper(trim($values[6])) !== 'NULL' ? (int)trim($values[6]) : null;
            // $semestr игнорируем
            $holat = !empty($values[8]) ? (int)trim($values[8]) : 1;

            // Проверяем, существует ли уже запись с таким old_id
            $existing = Group::where('old_id', $oldId)->first();
            if ($existing) {
                $this->command->line("Группа с old_id={$oldId} уже существует, пропускаем.");
                continue;
            }

            // Находим specialty по old_id
            $specialtyId = null;
            $specialty = null;
            if ($idIkhtisos) {
                $specialty = Specialty::where('old_id', $idIkhtisos)->first();
                $specialtyId = $specialty ? $specialty->id : null;
            }

            // Находим department по id
            $departmentId = null;
            if ($idKafedra) {
                $department = Department::where('id', $idKafedra)->first();
                $departmentId = $department ? $department->id : null;
            }

            // Если department_id не найден, но есть specialty, берем department_id из specialty
            if (!$departmentId && $specialty && $specialty->department_id) {
                $departmentId = $specialty->department_id;
            }

            // Преобразуем статус: 1 = active, 2 = suspended
            $status = $holat == 1 ? 'active' : ($holat == 2 ? 'suspended' : 'active');

            // specialty_id обязателен, пропускаем если не найден
            if (!$specialtyId) {
                $this->command->warn("Группа с old_id={$oldId} пропущена: не найдена specialty с old_id={$idIkhtisos}.");
                continue;
            }

            // department_id тоже обязателен, пропускаем если не найден
            if (!$departmentId) {
                $this->command->warn("Группа с old_id={$oldId} пропущена: не найден department (id_kafedra={$idKafedra}).");
                continue;
            }

            // Если нет department_id, но есть specialty, можно создать группу
            // department_id может быть nullable в некоторых случаях

            try {
                Group::create([
                    'old_id' => $oldId,
                    'name' => $name,
                    'course' => $course,
                    'specialty_id' => $specialtyId,
                    'department_id' => $departmentId,
                    'enrollment_year' => $soliDokhilshavi,
                    'graduation_year' => $soliKhonish,
                    'status' => $status,
                ]);
            } catch (\Exception $e) {
                $this->command->warn("Ошибка при создании группы с old_id={$oldId}: " . $e->getMessage());
                continue;
            }
        }

        $this->command->info("Импорт групп завершен.");
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
        $currentYear = date('Y');
        
        // Получаем кафедры и специальности
        $itDepartment = Department::where('code', 'IT')->first();
        $emDepartment = Department::where('code', 'EM')->first();
        
        $itSpecialty = Specialty::where('code', '09.03.02')->first();
        $piSpecialty = Specialty::where('code', '09.03.03')->first();

        if (!$itDepartment || !$itSpecialty) {
            $this->command->warn("Не найдены необходимые кафедры или специальности для создания групп по умолчанию.");
            return;
        }

        $groups = [
            [
                'name' => 'ИС-21-1',
                'enrollment_year' => 2021,
                'graduation_year' => 2025,
                'status' => 'active',
                'department_id' => $itDepartment->id,
                'course' => 3,
                'specialty_id' => $itSpecialty->id,
            ],
        ];

        foreach ($groups as $group) {
            Group::create($group);
        }
    }
}
