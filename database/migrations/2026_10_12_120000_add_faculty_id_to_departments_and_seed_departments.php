<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('departments', 'faculty_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->foreignId('faculty_id')->nullable()->constrained('faculties')->nullOnDelete();
            });
        }

        $now = now();
        $departments = [
            ['code' => 'KIT', 'name' => 'Кафедраи технологияҳои иттилоотӣ ва фанҳои дақиқ'],
            ['code' => 'KSN', 'name' => 'Кафедраи технологиҳои саноатӣ ва фанҳои табиӣ'],
            ['code' => 'KZP', 'name' => 'Кафедраи забонҳо, педагогикаи касбӣ ва фанҳои ҷамъиятӣ'],
            ['code' => 'KEM', 'name' => 'Кафедраи иқтисодиёт ва менеҷмент'],
        ];

        foreach ($departments as $department) {
            $exists = DB::table('departments')->where('code', $department['code'])->exists();
            if ($exists) {
                DB::table('departments')->where('code', $department['code'])->update([
                    'name' => $department['name'],
                    'updated_at' => $now,
                ]);
                continue;
            }

            DB::table('departments')->insert([
                'name' => $department['name'],
                'code' => $department['code'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('departments', 'faculty_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('faculty_id');
            });
        }
    }
};
