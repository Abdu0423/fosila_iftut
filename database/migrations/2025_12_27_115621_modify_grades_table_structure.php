<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            // Удаляем старые колонки
            $table->dropColumn([
                'grade_1',
                'grade_2',
                'grade_3',
                'grade_4',
                'grade_5'
            ]);
        });

        Schema::table('grades', function (Blueprint $table) {
            // Две оценки для рейтингового учителя
            $table->decimal('rating_teacher_1', 5, 2)->nullable()->after('student_id')
                ->comment('Рейтинговая оценка учителя 1');
            $table->decimal('rating_teacher_2', 5, 2)->nullable()->after('rating_teacher_1')
                ->comment('Рейтинговая оценка учителя 2');
            
            // Две оценки для рейтингового теста (максимальные из 3 попыток)
            $table->decimal('rating_test_1', 5, 2)->nullable()->after('rating_teacher_2')
                ->comment('Рейтинговая оценка теста 1 (максимальная из 3 попыток)');
            $table->decimal('rating_test_2', 5, 2)->nullable()->after('rating_test_1')
                ->comment('Рейтинговая оценка теста 2 (максимальная из 3 попыток)');
            
            // Последний экзамен
            $table->decimal('final_exam_grade', 5, 2)->nullable()->after('rating_test_2')
                ->comment('Оценка последнего экзамена (максимальная из 3 попыток для теста)');
            $table->enum('final_exam_type', ['teacher', 'test'])->nullable()->after('final_exam_grade')
                ->comment('Тип последнего экзамена: учитель или тест');
            
            // Итоговые оценки
            $table->decimal('final_grade_100', 5, 2)->nullable()->after('final_exam_type')
                ->comment('Итоговая 100-балловая оценка');
            $table->decimal('final_grade_10', 3, 1)->nullable()->after('final_grade_100')
                ->comment('Итоговая 10-балловая оценка');
            $table->string('final_grade_letter', 2)->nullable()->after('final_grade_10')
                ->comment('Итоговая буквенная оценка (F, D, D+, C-, C, C+, B-, B, B+, A-, A)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            // Удаляем новые колонки
            $table->dropColumn([
                'rating_teacher_1',
                'rating_teacher_2',
                'rating_test_1',
                'rating_test_2',
                'final_exam_grade',
                'final_exam_type',
                'final_grade_100',
                'final_grade_10',
                'final_grade_letter'
            ]);
        });

        Schema::table('grades', function (Blueprint $table) {
            // Восстанавливаем старые колонки
            $table->decimal('grade_1', 5, 2)->nullable()->comment('Оценка 1 - ставит учитель');
            $table->decimal('grade_2', 5, 2)->nullable()->comment('Оценка 2 - ставит учитель');
            $table->decimal('grade_3', 5, 2)->nullable()->comment('Оценка 3 - из теста');
            $table->decimal('grade_4', 5, 2)->nullable()->comment('Оценка 4 - из теста');
            $table->decimal('grade_5', 5, 2)->nullable()->comment('Оценка 5 - экзамен из теста');
        });
    }
};
