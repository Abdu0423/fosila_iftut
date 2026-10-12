<?php

use App\Http\Controllers\AuthController;

$registerOrgPanel = function (string $prefix, string $role) {
    Route::prefix($prefix)->middleware(['auth', 'role:'.$role, 'check.password.change'])->group(function () use ($prefix) {
        Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name($prefix.'.logout');
        Route::get('/', function () {
            return spa('Teacher/Dashboard');
        })->name($prefix.'.dashboard');

        Route::get('/schedules', [App\Http\Controllers\EducationDepartmentController::class, 'schedules'])->name($prefix.'.schedules.index');
        Route::get('/schedules/create', [App\Http\Controllers\EducationDepartmentController::class, 'createSchedule'])->name($prefix.'.schedules.create');
        Route::post('/schedules', [App\Http\Controllers\EducationDepartmentController::class, 'storeSchedule'])->name($prefix.'.schedules.store');
        Route::get('/schedules/{schedule}/edit', [App\Http\Controllers\EducationDepartmentController::class, 'editSchedule'])->name($prefix.'.schedules.edit');
        Route::put('/schedules/{schedule}', [App\Http\Controllers\EducationDepartmentController::class, 'updateSchedule'])->name($prefix.'.schedules.update');
        Route::post('/schedules/{schedule}/toggle-status', [App\Http\Controllers\EducationDepartmentController::class, 'toggleScheduleStatus'])->name($prefix.'.schedules.toggle-status');

        Route::get('/lessons', [App\Http\Controllers\Teacher\LessonController::class, 'index'])->name($prefix.'.lessons.index');
        Route::get('/lessons/schedule/{schedule}', [App\Http\Controllers\Teacher\LessonController::class, 'showSchedule'])->name($prefix.'.lessons.schedule');
        Route::get('/lessons/create', [App\Http\Controllers\Teacher\LessonController::class, 'create'])->name($prefix.'.lessons.create');
        Route::post('/lessons', [App\Http\Controllers\Teacher\LessonController::class, 'store'])->name($prefix.'.lessons.store');
        Route::get('/lessons/{lesson}', [App\Http\Controllers\Teacher\LessonController::class, 'show'])->name($prefix.'.lessons.show');
        Route::get('/lessons/{lesson}/edit', [App\Http\Controllers\Teacher\LessonController::class, 'edit'])->name($prefix.'.lessons.edit');
        Route::put('/lessons/{lesson}', [App\Http\Controllers\Teacher\LessonController::class, 'update'])->name($prefix.'.lessons.update');
        Route::delete('/lessons/{lesson}', [App\Http\Controllers\Teacher\LessonController::class, 'destroy'])->name($prefix.'.lessons.destroy');

        Route::get('/tests', [App\Http\Controllers\Teacher\TestController::class, 'index'])->name($prefix.'.tests.index');
        Route::get('/schedules/{schedule}/test', [App\Http\Controllers\Teacher\TestController::class, 'show'])->name($prefix.'.tests.show');
        Route::put('/tests/{test}', [App\Http\Controllers\Teacher\TestController::class, 'update'])->name($prefix.'.tests.update');
        Route::post('/tests/{test}/toggle-status', [App\Http\Controllers\Teacher\TestController::class, 'toggleStatus'])->name($prefix.'.tests.toggle-status');
        Route::post('/tests/{test}/questions', [App\Http\Controllers\Teacher\QuestionController::class, 'store'])->name($prefix.'.questions.store');
        Route::put('/tests/{test}/questions/{question}', [App\Http\Controllers\Teacher\QuestionController::class, 'update'])->name($prefix.'.questions.update');
        Route::delete('/tests/{test}/questions/{question}', [App\Http\Controllers\Teacher\QuestionController::class, 'destroy'])->name($prefix.'.questions.destroy');
        Route::patch('/tests/{test}/questions/reorder', [App\Http\Controllers\Teacher\QuestionController::class, 'reorder'])->name($prefix.'.questions.reorder');

        Route::get('/grades', [App\Http\Controllers\Teacher\GradeController::class, 'index'])->name($prefix.'.grades.index');
        Route::get('/grades/schedule/{schedule}', [App\Http\Controllers\Teacher\GradeController::class, 'getGrades'])->name($prefix.'.grades.get');
        Route::put('/grades/{grade}', [App\Http\Controllers\Teacher\GradeController::class, 'updateGrade'])->name($prefix.'.grades.update');
        Route::post('/grades/save-rating', [App\Http\Controllers\Teacher\GradeController::class, 'saveRating'])->name($prefix.'.grades.save-rating');

        Route::get('/ssr', [App\Http\Controllers\Teacher\SsrController::class, 'index'])->name($prefix.'.ssr.index');
        Route::get('/ssr/create', [App\Http\Controllers\Teacher\SsrController::class, 'create'])->name($prefix.'.ssr.create');
        Route::post('/ssr', [App\Http\Controllers\Teacher\SsrController::class, 'store'])->name($prefix.'.ssr.store');
        Route::get('/ssr/submission/{submission}', [App\Http\Controllers\Teacher\SsrController::class, 'showSubmission'])->name($prefix.'.ssr.submission');
        Route::post('/ssr/submission/{submission}/check', [App\Http\Controllers\Teacher\SsrController::class, 'checkSubmission'])->name($prefix.'.ssr.check');
        Route::get('/ssr/{task}', [App\Http\Controllers\Teacher\SsrController::class, 'show'])->name($prefix.'.ssr.show');
        Route::get('/ssr/{task}/edit', [App\Http\Controllers\Teacher\SsrController::class, 'edit'])->name($prefix.'.ssr.edit');
        Route::put('/ssr/{task}', [App\Http\Controllers\Teacher\SsrController::class, 'update'])->name($prefix.'.ssr.update');
        Route::post('/ssr/{task}/toggle-status', [App\Http\Controllers\Teacher\SsrController::class, 'toggleStatus'])->name($prefix.'.ssr.toggle-status');
        Route::delete('/ssr/{task}', [App\Http\Controllers\Teacher\SsrController::class, 'destroy'])->name($prefix.'.ssr.destroy');

        Route::get('/coursework', [App\Http\Controllers\Teacher\CourseworkController::class, 'index'])->name($prefix.'.coursework.index');
        Route::get('/coursework/submission/{submission}', [App\Http\Controllers\Teacher\CourseworkController::class, 'showSubmission'])->name($prefix.'.coursework.submission');
        Route::post('/coursework/submission/{submission}/check', [App\Http\Controllers\Teacher\CourseworkController::class, 'checkSubmission'])->name($prefix.'.coursework.check');
        Route::get('/coursework/{schedule}', [App\Http\Controllers\Teacher\CourseworkController::class, 'show'])->name($prefix.'.coursework.show');
        Route::post('/coursework/{schedule}', [App\Http\Controllers\Teacher\CourseworkController::class, 'store'])->name($prefix.'.coursework.store');
        Route::put('/coursework/{schedule}', [App\Http\Controllers\Teacher\CourseworkController::class, 'update'])->name($prefix.'.coursework.update');
        Route::post('/coursework/{schedule}/toggle-status', [App\Http\Controllers\Teacher\CourseworkController::class, 'toggleStatus'])->name($prefix.'.coursework.toggle-status');

        Route::get('/students', [App\Http\Controllers\Teacher\StudentController::class, 'index'])->name($prefix.'.students.index');
        Route::get('/students/group/{group}', [App\Http\Controllers\Teacher\StudentController::class, 'show'])->name($prefix.'.students.group');
        Route::get('/students/student/{student}', [App\Http\Controllers\Teacher\StudentController::class, 'showStudent'])->name($prefix.'.students.student');

        Route::get('/schedule', [App\Http\Controllers\Teacher\ScheduleController::class, 'index'])->name($prefix.'.schedule.index');
        Route::post('/schedule', [App\Http\Controllers\Teacher\ScheduleController::class, 'store'])->name($prefix.'.schedule.store');
        Route::get('/schedule/{schedule}', [App\Http\Controllers\Teacher\ScheduleController::class, 'show'])->name($prefix.'.schedule.show');
        Route::put('/schedule/{schedule}', [App\Http\Controllers\Teacher\ScheduleController::class, 'update'])->name($prefix.'.schedule.update');
        Route::post('/schedule/{schedule}/toggle-status', [App\Http\Controllers\Teacher\ScheduleController::class, 'toggleStatus'])->name($prefix.'.schedule.toggle-status');
        Route::delete('/schedule/{schedule}', [App\Http\Controllers\Teacher\ScheduleController::class, 'destroy'])->name($prefix.'.schedule.destroy');
        Route::post('/schedule/{schedule}/syllabuses', [App\Http\Controllers\Teacher\ScheduleController::class, 'addSyllabus'])->name($prefix.'.schedule.add-syllabus');
        Route::post('/schedule/{schedule}/syllabuses/upload', [App\Http\Controllers\Teacher\ScheduleController::class, 'uploadSyllabus'])->name($prefix.'.schedule.upload-syllabus');
        Route::delete('/schedule/{schedule}/syllabuses/{syllabus}', [App\Http\Controllers\Teacher\ScheduleController::class, 'removeSyllabus'])->name($prefix.'.schedule.remove-syllabus');
        Route::post('/schedule/{schedule}/lessons', [App\Http\Controllers\Teacher\ScheduleController::class, 'addLesson'])->name($prefix.'.schedule.add-lesson');
        Route::delete('/schedule/{schedule}/lessons/{lesson}', [App\Http\Controllers\Teacher\ScheduleController::class, 'removeLesson'])->name($prefix.'.schedule.remove-lesson');
        Route::patch('/schedule/{schedule}/lessons/reorder', [App\Http\Controllers\Teacher\ScheduleController::class, 'reorderLessons'])->name($prefix.'.schedule.reorder-lessons');

        Route::get('/syllabuses', [App\Http\Controllers\Teacher\SyllabusController::class, 'index'])->name($prefix.'.syllabuses.index');
        Route::get('/syllabuses/create', [App\Http\Controllers\Teacher\SyllabusController::class, 'create'])->name($prefix.'.syllabuses.create');
        Route::post('/syllabuses', [App\Http\Controllers\Teacher\SyllabusController::class, 'store'])->name($prefix.'.syllabuses.store');
        Route::get('/syllabuses/{syllabus}', [App\Http\Controllers\Teacher\SyllabusController::class, 'show'])->name($prefix.'.syllabuses.show');
        Route::get('/syllabuses/{syllabus}/edit', [App\Http\Controllers\Teacher\SyllabusController::class, 'edit'])->name($prefix.'.syllabuses.edit');
        Route::put('/syllabuses/{syllabus}', [App\Http\Controllers\Teacher\SyllabusController::class, 'update'])->name($prefix.'.syllabuses.update');
        Route::delete('/syllabuses/{syllabus}', [App\Http\Controllers\Teacher\SyllabusController::class, 'destroy'])->name($prefix.'.syllabuses.destroy');
        Route::get('/syllabuses/{syllabus}/download', [App\Http\Controllers\Teacher\SyllabusController::class, 'download'])->name($prefix.'.syllabuses.download');
        Route::get('/syllabuses/{syllabus}/preview', [App\Http\Controllers\Teacher\SyllabusController::class, 'preview'])->name($prefix.'.syllabuses.preview');

        Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name($prefix.'.profile.index');
        Route::post('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name($prefix.'.profile.password');

        Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name($prefix.'.chat.index');
        Route::get('/chat/{chat}', [App\Http\Controllers\ChatController::class, 'show'])->name($prefix.'.chat.show');
        Route::post('/chat', [App\Http\Controllers\ChatController::class, 'store'])->name($prefix.'.chat.store');
        Route::post('/chat/{chat}/messages', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name($prefix.'.chat.send-message');
        Route::get('/chat/{chat}/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name($prefix.'.chat.get-messages');
        Route::post('/chat/{chat}/read', [App\Http\Controllers\ChatController::class, 'markAsRead'])->name($prefix.'.chat.mark-read');
        Route::delete('/chat/{chat}/leave', [App\Http\Controllers\ChatController::class, 'leave'])->name($prefix.'.chat.leave');
    });
};

$registerOrgPanel('department', 'department');
$registerOrgPanel('faculty', 'faculty');
