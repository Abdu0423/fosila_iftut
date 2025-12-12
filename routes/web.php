<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Favicon route (чтобы избежать ошибок)
Route::get('/favicon.ico', function () {
    return response('', 204);
});

// Маршруты аутентификации (доступны для гостей)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Юридические страницы (доступны всем)
Route::get('/privacy', function () {
    return Inertia::render('Legal/PrivacyPolicy');
})->name('privacy');

Route::get('/terms', function () {
    return Inertia::render('Legal/TermsOfService');
})->name('terms');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Маршруты для смены языка
Route::post('/locale/change', [App\Http\Controllers\LocaleController::class, 'change'])->name('locale.change');
Route::get('/locale/current', [App\Http\Controllers\LocaleController::class, 'getCurrent'])->name('locale.current');

// Маршрут для смены пароля (доступен только авторизованным пользователям)
Route::middleware('auth')->group(function () {
    Route::get('/change-password', [App\Http\Controllers\ChangePasswordController::class, 'show'])->name('change-password');
    Route::post('/change-password', [App\Http\Controllers\ChangePasswordController::class, 'update'])->name('change-password.update');
});

// Маршруты для студентов
Route::prefix('student')->middleware(['auth', 'student', 'check.password.change'])->group(function () {
    // Выход из системы
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('student.logout');
    
    // Главная страница студента
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('student.dashboard');

    // Dashboard (альтернативный маршрут)
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('student.dashboard.alternative');

    // Курсы
    Route::get('/courses', function () {
        return Inertia::render('Courses/Index');
    })->name('student.courses.index');

    Route::get('/courses/{course}', function ($course) {
        return Inertia::render('Courses/Show', ['course' => $course]);
    })->name('student.courses.show');

    // Расписание
    Route::get('/schedule', [App\Http\Controllers\Student\ScheduleController::class, 'index'])->name('student.schedule.index');

    // Задания
    Route::get('/assignments', [App\Http\Controllers\Student\AssignmentController::class, 'index'])->name('student.assignments.index');
    Route::get('/assignments/{assignment}', [App\Http\Controllers\Student\AssignmentController::class, 'show'])->name('student.assignments.show');
    Route::post('/assignments/{assignment}/submit', [App\Http\Controllers\Student\AssignmentController::class, 'submit'])->name('student.assignments.submit');
    Route::put('/assignments/{assignment}/submissions/{submission}', [App\Http\Controllers\Student\AssignmentController::class, 'updateSubmission'])->name('student.assignments.update-submission');

    // Чат
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('student.chat.index');
    Route::get('/chat/{chat}', [App\Http\Controllers\ChatController::class, 'show'])->name('student.chat.show');
    Route::post('/chat', [App\Http\Controllers\ChatController::class, 'store'])->name('student.chat.store');
    Route::post('/chat/{chat}/messages', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('student.chat.send-message');
    Route::get('/chat/{chat}/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('student.chat.get-messages');
    Route::post('/chat/{chat}/read', [App\Http\Controllers\ChatController::class, 'markAsRead'])->name('student.chat.mark-read');
    Route::delete('/chat/{chat}/leave', [App\Http\Controllers\ChatController::class, 'leave'])->name('student.chat.leave');

    // Библиотека
    Route::get('/library', [App\Http\Controllers\Student\LibraryController::class, 'index'])->name('student.library.index');
    Route::get('/library/download', [App\Http\Controllers\Student\LibraryController::class, 'download'])->name('student.library.download');

    // Оценки
    Route::get('/grades', [App\Http\Controllers\Student\GradeController::class, 'index'])->name('student.grades.index');
    Route::get('/grades/{grade}', [App\Http\Controllers\Student\GradeController::class, 'show'])->name('student.grades.show');

    // Экзамены
    Route::get('/tests', [App\Http\Controllers\Student\TestController::class, 'index'])->name('student.tests.index');
    Route::get('/tests/{test}', [App\Http\Controllers\Student\TestController::class, 'show'])->name('student.tests.show');
    Route::post('/tests/{test}/start', [App\Http\Controllers\Student\TestController::class, 'startAttempt'])->name('student.tests.start');
    Route::post('/tests/{test}/submit', [App\Http\Controllers\Student\TestController::class, 'submitAttempt'])->name('student.tests.submit');

    // Профиль
    Route::get('/profile', function () {
        return Inertia::render('Profile/Index');
    })->name('student.profile.index');

    // Настройки
    Route::get('/settings', function () {
        return Inertia::render('Settings/Index');
    })->name('student.settings.index');
});

// Общие маршруты (для всех аутентифицированных пользователей)
Route::middleware('auth')->group(function () {
    // Главная страница (перенаправляет в зависимости от роли)
    Route::get('/', function () {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        } elseif ($user->isEducationDepartment()) {
            return redirect()->route('education.dashboard');
        } else {
            return redirect()->route('student.dashboard');
        }
    })->name('dashboard');

    // Dashboard (альтернативный маршрут)
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        } else {
            return redirect()->route('student.dashboard');
        }
    })->name('dashboard.alternative');

    // Тестовый маршрут для проверки уведомлений (удалить после тестирования)
    Route::get('/test-notification', function () {
        return redirect()->back()->with('success', 'Тестовое уведомление об успехе!');
    })->name('test.notification');
});

// Админ панель (требует аутентификации и прав администратора)
Route::prefix('admin')->middleware(['auth', 'admin', 'check.password.change'])->group(function () {
    // Выход из системы
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('admin.logout');
    
    // Главная страница админа
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Управление предметами
    Route::resource('subjects', App\Http\Controllers\Admin\SubjectController::class)->names('admin.subjects');
    Route::post('/subjects/{subject}/toggle-status', [App\Http\Controllers\Admin\SubjectController::class, 'toggleStatus'])->name('admin.subjects.toggle-status');
    Route::post('/subjects/bulk-action', [App\Http\Controllers\Admin\SubjectController::class, 'bulkAction'])->name('admin.subjects.bulk-action');
    Route::post('/subjects/{subject}/duplicate', [App\Http\Controllers\Admin\SubjectController::class, 'duplicate'])->name('admin.subjects.duplicate');
    Route::get('/subjects-export', [App\Http\Controllers\Admin\SubjectController::class, 'export'])->name('admin.subjects.export');
    
    // Управление пользователями
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/users/{user}/delete', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy.post');
    
    // Управление курсами
    Route::get('/courses', function () {
        return Inertia::render('Admin/Courses/Index');
    })->name('admin.courses.index');
    
    Route::get('/courses/create', function () {
        return Inertia::render('Admin/Courses/Create');
    })->name('admin.courses.create');
    
    Route::get('/courses/{course}/edit', function ($course) {
        return Inertia::render('Admin/Courses/Edit', ['course' => $course]);
    })->name('admin.courses.edit');
    
    // Управление заданиями
    Route::resource('assignments', App\Http\Controllers\Admin\AssignmentController::class)->names('admin.assignments');
    Route::post('/assignments/bulk-action', [App\Http\Controllers\Admin\AssignmentController::class, 'bulkAction'])->name('admin.assignments.bulk-action');
    
    // Управление библиотекой
    Route::get('/library', [App\Http\Controllers\Admin\LibraryController::class, 'index'])->name('admin.library.index');
    Route::post('/library', [App\Http\Controllers\Admin\LibraryController::class, 'store'])->name('admin.library.store');
    Route::get('/library/download', [App\Http\Controllers\Admin\LibraryController::class, 'download'])->name('admin.library.download');
    Route::delete('/library', [App\Http\Controllers\Admin\LibraryController::class, 'destroy'])->name('admin.library.destroy');
    Route::post('/library/bulk-delete', [App\Http\Controllers\Admin\LibraryController::class, 'bulkDelete'])->name('admin.library.bulk-delete');
    
    // Статистика и отчеты
    Route::get('/reports', function () {
        return Inertia::render('Admin/Reports/Index');
    })->name('admin.reports.index');
    
    // Настройки системы
    Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('admin.settings.index');
    Route::post('/settings/general', [App\Http\Controllers\Admin\SettingsController::class, 'updateGeneral'])->name('admin.settings.general');
    Route::post('/settings/files', [App\Http\Controllers\Admin\SettingsController::class, 'updateFileSettings'])->name('admin.settings.files');
    Route::post('/settings/system', [App\Http\Controllers\Admin\SettingsController::class, 'updateSystemSettings'])->name('admin.settings.system');
    Route::post('/settings/email', [App\Http\Controllers\Admin\SettingsController::class, 'updateEmailSettings'])->name('admin.settings.email');
    Route::post('/settings/profile', [App\Http\Controllers\Admin\SettingsController::class, 'updateProfile'])->name('admin.settings.profile');
    Route::post('/settings/test-email', [App\Http\Controllers\Admin\SettingsController::class, 'testEmail'])->name('admin.settings.test-email');
    Route::post('/settings/clear-cache', [App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('admin.settings.clear-cache');
    Route::post('/settings/backup-database', [App\Http\Controllers\Admin\SettingsController::class, 'backupDatabase'])->name('admin.settings.backup-database');
    
    // Управление силлабусами
    Route::get('/syllabuses', [App\Http\Controllers\Admin\SyllabusController::class, 'index'])->name('admin.syllabuses.index');
    Route::get('/syllabuses/create', [App\Http\Controllers\Admin\SyllabusController::class, 'create'])->name('admin.syllabuses.create');
    Route::post('/syllabuses', [App\Http\Controllers\Admin\SyllabusController::class, 'store'])->name('admin.syllabuses.store');
    Route::get('/syllabuses/{syllabus}', [App\Http\Controllers\Admin\SyllabusController::class, 'show'])->name('admin.syllabuses.show');
    Route::get('/syllabuses/{syllabus}/edit', [App\Http\Controllers\Admin\SyllabusController::class, 'edit'])->name('admin.syllabuses.edit');
    Route::put('/syllabuses/{syllabus}', [App\Http\Controllers\Admin\SyllabusController::class, 'update'])->name('admin.syllabuses.update');
    Route::delete('/syllabuses/{syllabus}', [App\Http\Controllers\Admin\SyllabusController::class, 'destroy'])->name('admin.syllabuses.destroy');
    Route::get('/syllabuses/{syllabus}/download', [App\Http\Controllers\Admin\SyllabusController::class, 'download'])->name('admin.syllabuses.download');
    Route::get('/syllabuses/{syllabus}/preview', [App\Http\Controllers\Admin\SyllabusController::class, 'preview'])->name('admin.syllabuses.preview');
    Route::get('/syllabuses/{syllabus}/content', [App\Http\Controllers\Admin\SyllabusController::class, 'getFileContent'])->name('admin.syllabuses.content');
    Route::get('/syllabuses/{syllabus}/pdf-viewer', [App\Http\Controllers\Admin\SyllabusController::class, 'getPdfViewerUrl'])->name('admin.syllabuses.pdf-viewer');
    Route::get('/syllabuses/{syllabus}/word-viewer', [App\Http\Controllers\Admin\SyllabusController::class, 'getWordViewerUrl'])->name('admin.syllabuses.word-viewer');
    Route::get('/syllabuses/{syllabus}/word-html', [App\Http\Controllers\Admin\SyllabusController::class, 'convertWordToHtml'])->name('admin.syllabuses.word-html');
    
    // Управление уроками
    Route::get('/lessons', [App\Http\Controllers\Admin\LessonController::class, 'index'])->name('admin.lessons.index');
    Route::get('/lessons/create', [App\Http\Controllers\Admin\LessonController::class, 'create'])->name('admin.lessons.create');
    Route::post('/lessons', [App\Http\Controllers\Admin\LessonController::class, 'store'])->name('admin.lessons.store');
    Route::get('/lessons/{lesson}', [App\Http\Controllers\Admin\LessonController::class, 'show'])->name('admin.lessons.show');
    Route::get('/lessons/{lesson}/edit', [App\Http\Controllers\Admin\LessonController::class, 'edit'])->name('admin.lessons.edit');
    Route::put('/lessons/{lesson}', [App\Http\Controllers\Admin\LessonController::class, 'update'])->name('admin.lessons.update');
    Route::delete('/lessons/{lesson}', [App\Http\Controllers\Admin\LessonController::class, 'destroy'])->name('admin.lessons.destroy');
    Route::get('/lessons/{lesson}/materials', [App\Http\Controllers\Admin\LessonController::class, 'materials'])->name('admin.lessons.materials');
    
    // Дополнительные маршруты для уроков
    Route::post('/lessons/filter', [App\Http\Controllers\Admin\LessonController::class, 'filter'])->name('admin.lessons.filter');
    Route::post('/lessons/clear-filters', [App\Http\Controllers\Admin\LessonController::class, 'clearFilters'])->name('admin.lessons.clear-filters');
    Route::get('/lessons/{lesson}/download', [App\Http\Controllers\Admin\LessonController::class, 'download'])->name('admin.lessons.download');
    
    // Управление тестами
    Route::get('/tests', function () {
        return Inertia::render('Admin/Tests/Index');
    })->name('admin.tests.index');
    
    Route::get('/tests/create', function () {
        return Inertia::render('Admin/Tests/Create');
    })->name('admin.tests.create');
    
    Route::get('/tests/{test}/edit', function ($test) {
        return Inertia::render('Admin/Tests/Edit', ['test' => $test]);
    })->name('admin.tests.edit');
    
    Route::get('/tests/{test}/questions', function ($test) {
        return Inertia::render('Admin/Tests/Questions', ['test' => $test]);
    })->name('admin.tests.questions');
    
    // Управление оценками
    Route::get('/grades', function () {
        return Inertia::render('Admin/Grades/Index');
    })->name('admin.grades.index');
    
    Route::get('/grades/students', function () {
        return Inertia::render('Admin/Grades/Students');
    })->name('admin.grades.students');
    
    Route::get('/grades/courses', function () {
        return Inertia::render('Admin/Grades/Courses');
    })->name('admin.grades.courses');
    
    // Управление расписанием (специфичные роуты ПЕРЕД resource)
    Route::get('/schedules/analytics', [App\Http\Controllers\Admin\ScheduleController::class, 'analytics'])->name('admin.schedules.analytics');
    Route::get('/schedules/bulk-create', [App\Http\Controllers\Admin\ScheduleController::class, 'bulkCreate'])->name('admin.schedules.bulk-create');
    Route::post('/schedules/bulk-store', [App\Http\Controllers\Admin\ScheduleController::class, 'bulkStore'])->name('admin.schedules.bulk-store');
    Route::get('/schedules/export', [App\Http\Controllers\Admin\ScheduleController::class, 'export'])->name('admin.schedules.export');
    Route::post('/schedules/export', [App\Http\Controllers\Admin\ScheduleController::class, 'export'])->name('admin.schedules.export.post');
    Route::post('/schedules/import', [App\Http\Controllers\Admin\ScheduleController::class, 'import'])->name('admin.schedules.import');
    Route::post('/schedules/bulk-action', [App\Http\Controllers\Admin\ScheduleController::class, 'bulkAction'])->name('admin.schedules.bulk-action');
    Route::post('/schedules/{schedule}/toggle-status', [App\Http\Controllers\Admin\ScheduleController::class, 'toggleStatus'])->name('admin.schedules.toggle-status');
    Route::post('/schedules/{schedule}/duplicate', [App\Http\Controllers\Admin\ScheduleController::class, 'duplicate'])->name('admin.schedules.duplicate');
    
    // Управление уроками в расписании
    Route::get('/schedules/{schedule}/lessons', [App\Http\Controllers\Admin\ScheduleController::class, 'lessons'])->name('admin.schedules.lessons');
    Route::post('/schedules/{schedule}/lessons', [App\Http\Controllers\Admin\ScheduleController::class, 'addLesson'])->name('admin.schedules.lessons.add');
    Route::delete('/schedules/{schedule}/lessons/{lesson}', [App\Http\Controllers\Admin\ScheduleController::class, 'removeLesson'])->name('admin.schedules.lessons.remove');
    Route::patch('/schedules/{schedule}/lessons/reorder', [App\Http\Controllers\Admin\ScheduleController::class, 'reorderLessons'])->name('admin.schedules.lessons.reorder');
    
    // Resource роут должен быть ПОСЛЕДНИМ
    Route::resource('schedules', App\Http\Controllers\Admin\ScheduleController::class)->names('admin.schedules');
    
    Route::resource('tests', App\Http\Controllers\Admin\TestController::class)->names('admin.tests');
Route::get('/tests/{test}/questions', [App\Http\Controllers\Admin\TestController::class, 'questions'])->name('admin.tests.questions');
Route::post('/tests/{test}/questions', [App\Http\Controllers\Admin\TestController::class, 'storeQuestion'])->name('admin.tests.questions.store');
Route::put('/tests/{test}/questions/{question}', [App\Http\Controllers\Admin\TestController::class, 'updateQuestion'])->name('admin.tests.questions.update');
Route::delete('/tests/{test}/questions/{question}', [App\Http\Controllers\Admin\TestController::class, 'destroyQuestion'])->name('admin.tests.questions.destroy');
Route::get('/tests/{test}/preview', [App\Http\Controllers\Admin\TestController::class, 'preview'])->name('admin.tests.preview');
    Route::get('/test-export', function() {
        return response('test')
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="test.txt"');
    })->name('admin.test-export');
});

// Тестовый маршрут без middleware для проверки
Route::get('/test-export-public', function() {
    return response('test public')
        ->header('Content-Type', 'text/plain')
        ->header('Content-Disposition', 'attachment; filename="test-public.txt"');
})->name('admin.test-export-public');

// Тестовый маршрут для проверки экспорта расписания
Route::get('/test-schedule-export', function() {
    try {
        $schedules = App\Models\Schedule::with(['lesson', 'teacher', 'group'])->get();
        return response()->json([
            'success' => true,
            'count' => $schedules->count(),
            'sample' => $schedules->first()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('admin.test-schedule-export');

// Тестовый маршрут для экспорта без middleware
Route::get('/test-export-schedule', function() {
    try {
        $schedules = App\Models\Schedule::with(['lesson', 'teacher', 'group'])->get();
        
        $filename = 'test_schedules_' . date('Y-m-d_H-i-s') . '.csv';
        $csvContent = '';
        $csvContent .= "\xEF\xBB\xBF"; // BOM для кириллицы
        
        $headers = ['ID', 'Урок', 'Преподаватель', 'Группа', 'Семестр', 'Кредиты', 'Год обучения', 'Порядок', 'Запланированная дата', 'Статус', 'Дата создания'];
        $csvContent .= implode(';', $headers) . "\n";
        
        foreach ($schedules as $schedule) {
            $row = [
                $schedule->id,
                $schedule->lesson->name ?? 'Не указано',
                $schedule->teacher->name ?? 'Не указано',
                $schedule->group->name ?? 'Не указано',
                $schedule->semester,
                $schedule->credits,
                $schedule->study_year,
                $schedule->order,
                $schedule->scheduled_at ? $schedule->scheduled_at->format('Y-m-d H:i:s') : 'Не указано',
                $schedule->is_active ? 'Активно' : 'Неактивно',
                $schedule->created_at->format('Y-m-d H:i:s')
            ];
            $csvContent .= implode(';', $row) . "\n";
        }
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('admin.test-export-schedule');

// Маршруты для учителей
Route::prefix('teacher')->middleware(['auth', 'teacher', 'check.password.change'])->group(function () {
    // Выход из системы
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('teacher.logout');
    
    // Главная страница учителя
    Route::get('/', function () {
        return Inertia::render('Teacher/Dashboard');
    })->name('teacher.dashboard');
    
    // Мои курсы
    Route::get('/courses', function () {
        return Inertia::render('Teacher/Courses/Index');
    })->name('teacher.courses.index');
    
    Route::get('/courses/{course}', function ($course) {
        return Inertia::render('Teacher/Courses/Show', ['course' => $course]);
    })->name('teacher.courses.show');
    
    // Мои уроки
    Route::get('/lessons', [App\Http\Controllers\Teacher\LessonController::class, 'index'])->name('teacher.lessons.index');
    Route::get('/lessons/schedule/{schedule}', [App\Http\Controllers\Teacher\LessonController::class, 'showSchedule'])->name('teacher.lessons.schedule');
    Route::get('/lessons/create', [App\Http\Controllers\Teacher\LessonController::class, 'create'])->name('teacher.lessons.create');
    Route::post('/lessons', [App\Http\Controllers\Teacher\LessonController::class, 'store'])->name('teacher.lessons.store');
    Route::get('/lessons/{lesson}', [App\Http\Controllers\Teacher\LessonController::class, 'show'])->name('teacher.lessons.show');
    Route::get('/lessons/{lesson}/edit', [App\Http\Controllers\Teacher\LessonController::class, 'edit'])->name('teacher.lessons.edit');
    Route::put('/lessons/{lesson}', [App\Http\Controllers\Teacher\LessonController::class, 'update'])->name('teacher.lessons.update');
    Route::delete('/lessons/{lesson}', [App\Http\Controllers\Teacher\LessonController::class, 'destroy'])->name('teacher.lessons.destroy');
    
    // Мои тесты (привязаны к расписаниям)
    Route::get('/tests', [App\Http\Controllers\Teacher\TestController::class, 'index'])->name('teacher.tests.index');
    Route::get('/schedules/{schedule}/test', [App\Http\Controllers\Teacher\TestController::class, 'show'])->name('teacher.tests.show');
    Route::put('/tests/{test}', [App\Http\Controllers\Teacher\TestController::class, 'update'])->name('teacher.tests.update');
    Route::post('/tests/{test}/toggle-status', [App\Http\Controllers\Teacher\TestController::class, 'toggleStatus'])->name('teacher.tests.toggle-status');
    
    // Управление вопросами теста
    Route::post('/tests/{test}/questions', [App\Http\Controllers\Teacher\QuestionController::class, 'store'])->name('teacher.questions.store');
    Route::put('/tests/{test}/questions/{question}', [App\Http\Controllers\Teacher\QuestionController::class, 'update'])->name('teacher.questions.update');
    Route::delete('/tests/{test}/questions/{question}', [App\Http\Controllers\Teacher\QuestionController::class, 'destroy'])->name('teacher.questions.destroy');
    Route::patch('/tests/{test}/questions/reorder', [App\Http\Controllers\Teacher\QuestionController::class, 'reorder'])->name('teacher.questions.reorder');
    
    // Оценки студентов
    Route::get('/grades', [App\Http\Controllers\Teacher\GradeController::class, 'index'])->name('teacher.grades.index');
    Route::get('/grades/schedule/{schedule}', [App\Http\Controllers\Teacher\GradeController::class, 'getGrades'])->name('teacher.grades.get');
    Route::put('/grades/{grade}', [App\Http\Controllers\Teacher\GradeController::class, 'updateGrade'])->name('teacher.grades.update');
    
    // Мои студенты
    Route::get('/students', [App\Http\Controllers\Teacher\StudentController::class, 'index'])->name('teacher.students.index');
    Route::get('/students/group/{group}', [App\Http\Controllers\Teacher\StudentController::class, 'show'])->name('teacher.students.group');
    Route::get('/students/student/{student}', [App\Http\Controllers\Teacher\StudentController::class, 'showStudent'])->name('teacher.students.student');
    
    // Расписание
    Route::get('/schedule', [App\Http\Controllers\Teacher\ScheduleController::class, 'index'])->name('teacher.schedule.index');
    Route::post('/schedule', [App\Http\Controllers\Teacher\ScheduleController::class, 'store'])->name('teacher.schedule.store');
    Route::get('/schedule/{schedule}', [App\Http\Controllers\Teacher\ScheduleController::class, 'show'])->name('teacher.schedule.show');
    Route::put('/schedule/{schedule}', [App\Http\Controllers\Teacher\ScheduleController::class, 'update'])->name('teacher.schedule.update');
    Route::delete('/schedule/{schedule}', [App\Http\Controllers\Teacher\ScheduleController::class, 'destroy'])->name('teacher.schedule.destroy');
    
    // Управление силлабусами в расписании
    Route::post('/schedule/{schedule}/syllabuses', [App\Http\Controllers\Teacher\ScheduleController::class, 'addSyllabus'])->name('teacher.schedule.add-syllabus');
    Route::post('/schedule/{schedule}/syllabuses/upload', [App\Http\Controllers\Teacher\ScheduleController::class, 'uploadSyllabus'])->name('teacher.schedule.upload-syllabus');
    Route::delete('/schedule/{schedule}/syllabuses/{syllabus}', [App\Http\Controllers\Teacher\ScheduleController::class, 'removeSyllabus'])->name('teacher.schedule.remove-syllabus');
    
    // Управление уроками в расписании
    Route::post('/schedule/{schedule}/lessons', [App\Http\Controllers\Teacher\ScheduleController::class, 'addLesson'])->name('teacher.schedule.add-lesson');
    Route::delete('/schedule/{schedule}/lessons/{lesson}', [App\Http\Controllers\Teacher\ScheduleController::class, 'removeLesson'])->name('teacher.schedule.remove-lesson');
    Route::patch('/schedule/{schedule}/lessons/reorder', [App\Http\Controllers\Teacher\ScheduleController::class, 'reorderLessons'])->name('teacher.schedule.reorder-lessons');
    
    // Управление силлабусами
    Route::get('/syllabuses', [App\Http\Controllers\Teacher\SyllabusController::class, 'index'])->name('teacher.syllabuses.index');
    Route::get('/syllabuses/create', [App\Http\Controllers\Teacher\SyllabusController::class, 'create'])->name('teacher.syllabuses.create');
    Route::post('/syllabuses', [App\Http\Controllers\Teacher\SyllabusController::class, 'store'])->name('teacher.syllabuses.store');
    Route::get('/syllabuses/{syllabus}', [App\Http\Controllers\Teacher\SyllabusController::class, 'show'])->name('teacher.syllabuses.show');
    Route::get('/syllabuses/{syllabus}/edit', [App\Http\Controllers\Teacher\SyllabusController::class, 'edit'])->name('teacher.syllabuses.edit');
    Route::put('/syllabuses/{syllabus}', [App\Http\Controllers\Teacher\SyllabusController::class, 'update'])->name('teacher.syllabuses.update');
    Route::delete('/syllabuses/{syllabus}', [App\Http\Controllers\Teacher\SyllabusController::class, 'destroy'])->name('teacher.syllabuses.destroy');
    Route::get('/syllabuses/{syllabus}/download', [App\Http\Controllers\Teacher\SyllabusController::class, 'download'])->name('teacher.syllabuses.download');
    Route::get('/syllabuses/{syllabus}/preview', [App\Http\Controllers\Teacher\SyllabusController::class, 'preview'])->name('teacher.syllabuses.preview');
    
    // Материалы
    Route::get('/materials', function () {
        return Inertia::render('Teacher/Materials/Index');
    })->name('teacher.materials.index');
    
    Route::get('/materials/create', function () {
        return Inertia::render('Teacher/Materials/Create');
    })->name('teacher.materials.create');
    
    // Отчеты
    Route::get('/reports', function () {
        return Inertia::render('Teacher/Reports/Index');
    })->name('teacher.reports.index');
    
    // Профиль
    Route::get('/profile', function () {
        return Inertia::render('Teacher/Profile/Index');
    })->name('teacher.profile.index');
    
    // Настройки
    Route::get('/settings', function () {
        return Inertia::render('Teacher/Settings/Index');
    })->name('teacher.settings.index');
    
    // Чат
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('teacher.chat.index');
    Route::get('/chat/{chat}', [App\Http\Controllers\ChatController::class, 'show'])->name('teacher.chat.show');
    Route::post('/chat', [App\Http\Controllers\ChatController::class, 'store'])->name('teacher.chat.store');
    Route::post('/chat/{chat}/messages', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('teacher.chat.send-message');
    Route::get('/chat/{chat}/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('teacher.chat.get-messages');
    Route::post('/chat/{chat}/read', [App\Http\Controllers\ChatController::class, 'markAsRead'])->name('teacher.chat.mark-read');
    Route::delete('/chat/{chat}/leave', [App\Http\Controllers\ChatController::class, 'leave'])->name('teacher.chat.leave');
});

// Маршруты для отдела образования
Route::prefix('education')->middleware(['auth', 'education.department', 'check.password.change'])->group(function () {
    // Dashboard
    Route::get('/', [App\Http\Controllers\EducationDepartmentController::class, 'dashboard'])->name('education.dashboard');
    Route::get('/dashboard', [App\Http\Controllers\EducationDepartmentController::class, 'dashboard'])->name('education.dashboard.alternative');
    
    // Пользователи
    Route::get('/users', [App\Http\Controllers\EducationDepartmentController::class, 'users'])->name('education.users.index');
    
    // Группы
    Route::get('/groups', [App\Http\Controllers\EducationDepartmentController::class, 'groups'])->name('education.groups.index');
    
    // Расписания
    Route::get('/schedules', [App\Http\Controllers\EducationDepartmentController::class, 'schedules'])->name('education.schedules.index');
    Route::get('/schedules/create', [App\Http\Controllers\EducationDepartmentController::class, 'createSchedule'])->name('education.schedules.create');
    Route::post('/schedules', [App\Http\Controllers\EducationDepartmentController::class, 'storeSchedule'])->name('education.schedules.store');
    Route::get('/schedules/{schedule}/edit', [App\Http\Controllers\EducationDepartmentController::class, 'editSchedule'])->name('education.schedules.edit');
    Route::put('/schedules/{schedule}', [App\Http\Controllers\EducationDepartmentController::class, 'updateSchedule'])->name('education.schedules.update');
    
    // Предметы
    Route::get('/subjects', [App\Http\Controllers\EducationDepartmentController::class, 'subjects'])->name('education.subjects.index');
    Route::get('/subjects/create', [App\Http\Controllers\EducationDepartmentController::class, 'createSubject'])->name('education.subjects.create');
    Route::post('/subjects', [App\Http\Controllers\EducationDepartmentController::class, 'storeSubject'])->name('education.subjects.store');
    Route::get('/subjects/{subject}/edit', [App\Http\Controllers\EducationDepartmentController::class, 'editSubject'])->name('education.subjects.edit');
    Route::put('/subjects/{subject}', [App\Http\Controllers\EducationDepartmentController::class, 'updateSubject'])->name('education.subjects.update');
});

// Тестовые маршруты для SMS (удалить в production или защитить middleware)
Route::prefix('sms-test')->middleware('auth')->group(function () {
    Route::post('/send', [App\Http\Controllers\SmsTestController::class, 'send'])->name('sms.test.send');
    Route::post('/send-verification', [App\Http\Controllers\SmsTestController::class, 'sendVerificationCode'])->name('sms.test.verification');
    Route::get('/check-config', [App\Http\Controllers\SmsTestController::class, 'checkConfig'])->name('sms.test.config');
});
