<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SpaController;

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

Route::get('/api/me', [SpaController::class, 'me'])->name('spa.me');

// Маршруты аутентификации (доступны для гостей)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    // Восстановление пароля через SMS
    Route::get('/password/forgot', [App\Http\Controllers\PasswordResetController::class, 'showForgotPassword'])->name('password.forgot');
    Route::post('/password/forgot/send-code', [App\Http\Controllers\PasswordResetController::class, 'sendCode'])->name('password.send-code');
    Route::get('/password/reset', [App\Http\Controllers\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [App\Http\Controllers\PasswordResetController::class, 'resetPassword'])->name('password.reset.post');
});

// Юридические страницы (доступны всем)
Route::get('/privacy', function () {
    return spa('Legal/PrivacyPolicy');
})->name('privacy');

Route::get('/terms', function () {
    return spa('Legal/TermsOfService');
})->name('terms');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Маршрут для смены языка
Route::post('/set-locale', [App\Http\Controllers\LocaleController::class, 'setLocale'])->name('locale.set');

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
        return spa('Dashboard');
    })->name('student.dashboard');

    // Dashboard (альтернативный маршрут)
    Route::get('/dashboard', function () {
        return spa('Dashboard');
    })->name('student.dashboard.alternative');

    // Курсы
    Route::get('/courses', function () {
        return spa('Courses/Index');
    })->name('student.courses.index');

    Route::get('/courses/{course}', function ($course) {
        return spa('Courses/Show', ['course' => $course]);
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

    // ССР (Самостоятельная студенческая работа)
    Route::get('/ssr', [App\Http\Controllers\Student\SsrController::class, 'index'])->name('student.ssr.index');
    Route::get('/ssr/{task}', [App\Http\Controllers\Student\SsrController::class, 'show'])->name('student.ssr.show');
    Route::post('/ssr/{task}/select-topic', [App\Http\Controllers\Student\SsrController::class, 'selectTopic'])->name('student.ssr.select-topic');
    Route::post('/ssr/{task}/save', [App\Http\Controllers\Student\SsrController::class, 'saveWork'])->name('student.ssr.save');
    Route::post('/ssr/{task}/submit', [App\Http\Controllers\Student\SsrController::class, 'submitWork'])->name('student.ssr.submit');
    Route::delete('/ssr/{task}/file', [App\Http\Controllers\Student\SsrController::class, 'deleteFile'])->name('student.ssr.delete-file');

    // Курсовая работа
    Route::get('/coursework', [App\Http\Controllers\Student\CourseworkController::class, 'index'])->name('student.coursework.index');
    Route::get('/coursework/{schedule}', [App\Http\Controllers\Student\CourseworkController::class, 'show'])->name('student.coursework.show');
    Route::post('/coursework/{schedule}/select-topic', [App\Http\Controllers\Student\CourseworkController::class, 'selectTopic'])->name('student.coursework.select-topic');
    Route::post('/coursework/{schedule}/save', [App\Http\Controllers\Student\CourseworkController::class, 'saveWork'])->name('student.coursework.save');
    Route::post('/coursework/{schedule}/submit', [App\Http\Controllers\Student\CourseworkController::class, 'submitWork'])->name('student.coursework.submit');
    Route::delete('/coursework/{schedule}/file', [App\Http\Controllers\Student\CourseworkController::class, 'deleteFile'])->name('student.coursework.delete-file');

    // Профиль
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('student.profile.index');
    Route::post('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('student.profile.password');

    // Настройки
    Route::get('/settings', function () {
        return spa('Settings/Index');
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
        } elseif ($user->isRegistrationCenter()) {
            return redirect()->route('registration.dashboard');
        } elseif ($user->isDepartment()) {
            return redirect()->route('department.dashboard');
        } elseif ($user->isFaculty()) {
            return redirect()->route('faculty.dashboard');
        } elseif ($user->isStudent()) {
            return redirect()->route('student.dashboard');
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
        } elseif ($user->isEducationDepartment()) {
            return redirect()->route('education.dashboard');
        } elseif ($user->isRegistrationCenter()) {
            return redirect()->route('registration.dashboard');
        } elseif ($user->isDepartment()) {
            return redirect()->route('department.dashboard');
        } elseif ($user->isFaculty()) {
            return redirect()->route('faculty.dashboard');
        } elseif ($user->isStudent()) {
            return redirect()->route('student.dashboard');
        } else {
            return redirect()->route('student.dashboard');
        }
    })->name('dashboard.alternative');

    // Тестовый маршрут для проверки уведомлений (удалить после тестирования)
    Route::get('/test-notification', function () {
        return redirect()->back()->with('success', 'Тестовое уведомление об успехе!');
    })->name('test.notification');
});

// Управление пользователями (администратор или отдел образования)
Route::prefix('admin')->middleware(['auth', 'staff', 'check.password.change'])->group(function () {
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/users/{user}/delete', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy.post');
});

// Админ панель (требует аутентификации и прав администратора)
Route::prefix('admin')->middleware(['auth', 'staff', 'check.password.change'])->group(function () {
    // Выход из системы
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('admin.logout');
    
    // Главная страница админа
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    Route::middleware('admin')->group(function () {
        Route::get('/access', [App\Http\Controllers\Admin\AccessController::class, 'index'])->name('admin.access.index');
        Route::post('/access', [App\Http\Controllers\Admin\AccessController::class, 'update'])->name('admin.access.update');
    });
    
    // Факультеты и кафедры
    Route::resource('faculties', App\Http\Controllers\Admin\FacultyController::class)->names('admin.faculties');
    Route::post('/faculties/{faculty}/toggle-status', [App\Http\Controllers\Admin\FacultyController::class, 'toggleStatus'])->name('admin.faculties.toggle-status');
    Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class)->names('admin.departments');
    Route::post('/departments/{department}/toggle-status', [App\Http\Controllers\Admin\DepartmentController::class, 'toggleStatus'])->name('admin.departments.toggle-status');

    // Управление предметами
    Route::resource('subjects', App\Http\Controllers\Admin\SubjectController::class)->names('admin.subjects');
    Route::post('/subjects/{subject}/toggle-status', [App\Http\Controllers\Admin\SubjectController::class, 'toggleStatus'])->name('admin.subjects.toggle-status');
    Route::post('/subjects/bulk-action', [App\Http\Controllers\Admin\SubjectController::class, 'bulkAction'])->name('admin.subjects.bulk-action');
    Route::post('/subjects/{subject}/duplicate', [App\Http\Controllers\Admin\SubjectController::class, 'duplicate'])->name('admin.subjects.duplicate');
    Route::get('/subjects-export', [App\Http\Controllers\Admin\SubjectController::class, 'export'])->name('admin.subjects.export');
    
    // Управление курсами
    Route::get('/courses', function () {
        return spa('Admin/Courses/Index');
    })->name('admin.courses.index');
    
    Route::get('/courses/create', function () {
        return spa('Admin/Courses/Create');
    })->name('admin.courses.create');
    
    Route::get('/courses/{course}/edit', function ($course) {
        return spa('Admin/Courses/Edit', ['course' => $course]);
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
        return spa('Admin/Reports/Index');
    })->name('admin.reports.index');
    
    // Чат
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('admin.chat.index');
    Route::get('/chat/{chat}', [App\Http\Controllers\ChatController::class, 'show'])->name('admin.chat.show');
    Route::post('/chat', [App\Http\Controllers\ChatController::class, 'store'])->name('admin.chat.store');
    Route::post('/chat/{chat}/messages', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('admin.chat.send-message');
    Route::get('/chat/{chat}/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('admin.chat.get-messages');
    Route::post('/chat/{chat}/read', [App\Http\Controllers\ChatController::class, 'markAsRead'])->name('admin.chat.mark-read');
    Route::delete('/chat/{chat}/leave', [App\Http\Controllers\ChatController::class, 'leave'])->name('admin.chat.leave');

    // Профиль
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('admin.profile.index');
    Route::post('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('admin.profile.password');

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
    
    // Отправка SMS
    Route::get('/sms', [App\Http\Controllers\Admin\SmsController::class, 'index'])->name('admin.sms.index');
    Route::post('/sms/send-credentials', [App\Http\Controllers\Admin\SmsController::class, 'sendCredentials'])->name('admin.sms.send-credentials');
    Route::post('/sms/send-custom', [App\Http\Controllers\Admin\SmsController::class, 'sendCustom'])->name('admin.sms.send-custom');
    
    // Управление студентами
    Route::get('/students', [App\Http\Controllers\Admin\StudentManagementController::class, 'index'])->name('admin.students.index');
    Route::get('/students/transfers', [App\Http\Controllers\Admin\StudentManagementController::class, 'transfers'])->name('admin.students.transfers');
    Route::get('/students/transfers/create', [App\Http\Controllers\Admin\StudentManagementController::class, 'createTransfer'])->name('admin.students.transfers.create');
    Route::post('/students/transfers', [App\Http\Controllers\Admin\StudentManagementController::class, 'storeTransfer'])->name('admin.students.transfers.store');
    
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
    
    // Управление оценками
    Route::get('/grades', function () {
        return spa('Admin/Grades/Index');
    })->name('admin.grades.index');
    
    Route::get('/grades/students', function () {
        return spa('Admin/Grades/Students');
    })->name('admin.grades.students');
    
    Route::get('/grades/courses', function () {
        return spa('Admin/Grades/Courses');
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
    Route::put('/schedules/{schedule}/teacher', [App\Http\Controllers\Admin\ScheduleController::class, 'assignTeacher'])->name('admin.schedules.assign-teacher');
    
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
        $schedules = App\Models\Schedule::with(['subject', 'teacher', 'group'])->get();
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
        $schedules = App\Models\Schedule::with(['subject', 'teacher', 'group'])->get();
        
        $filename = 'test_schedules_' . date('Y-m-d_H-i-s') . '.csv';
        $csvContent = '';
        $csvContent .= "\xEF\xBB\xBF"; // BOM для кириллицы
        
        $headers = ['ID', 'Урок', 'Преподаватель', 'Группа', 'Семестр', 'Кредиты', 'Год обучения', 'Порядок', 'Запланированная дата', 'Статус', 'Дата создания'];
        $csvContent .= implode(';', $headers) . "\n";
        
        foreach ($schedules as $schedule) {
            $row = [
                $schedule->id,
                $schedule->subject->name ?? 'Не указано',
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
Route::prefix('teacher')->middleware(['auth', 'staff', 'check.password.change'])->group(function () {
    // Выход из системы
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('teacher.logout');
    
    // Главная страница учителя
    Route::get('/', function () {
        return spa('Teacher/Dashboard');
    })->name('teacher.dashboard');
    
    // Мои курсы
    Route::get('/courses', function () {
        return spa('Teacher/Courses/Index');
    })->name('teacher.courses.index');
    
    Route::get('/courses/{course}', function ($course) {
        return spa('Teacher/Courses/Show', ['course' => $course]);
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
    Route::post('/grades/save-rating', [App\Http\Controllers\Teacher\GradeController::class, 'saveRating'])->name('teacher.grades.save-rating');
    
    // ССР (Самостоятельная студенческая работа)
    Route::get('/ssr', [App\Http\Controllers\Teacher\SsrController::class, 'index'])->name('teacher.ssr.index');
    Route::get('/ssr/create', [App\Http\Controllers\Teacher\SsrController::class, 'create'])->name('teacher.ssr.create');
    Route::post('/ssr', [App\Http\Controllers\Teacher\SsrController::class, 'store'])->name('teacher.ssr.store');
    Route::get('/ssr/{task}', [App\Http\Controllers\Teacher\SsrController::class, 'show'])->name('teacher.ssr.show');
    Route::get('/ssr/{task}/edit', [App\Http\Controllers\Teacher\SsrController::class, 'edit'])->name('teacher.ssr.edit');
    Route::put('/ssr/{task}', [App\Http\Controllers\Teacher\SsrController::class, 'update'])->name('teacher.ssr.update');
    Route::post('/ssr/{task}/toggle-status', [App\Http\Controllers\Teacher\SsrController::class, 'toggleStatus'])->name('teacher.ssr.toggle-status');
    Route::delete('/ssr/{task}', [App\Http\Controllers\Teacher\SsrController::class, 'destroy'])->name('teacher.ssr.destroy');
    Route::get('/ssr/submission/{submission}', [App\Http\Controllers\Teacher\SsrController::class, 'showSubmission'])->name('teacher.ssr.submission');
    Route::post('/ssr/submission/{submission}/check', [App\Http\Controllers\Teacher\SsrController::class, 'checkSubmission'])->name('teacher.ssr.check');
    
    // Курсовая работа
    Route::get('/coursework', [App\Http\Controllers\Teacher\CourseworkController::class, 'index'])->name('teacher.coursework.index');
    Route::get('/coursework/{schedule}', [App\Http\Controllers\Teacher\CourseworkController::class, 'show'])->name('teacher.coursework.show');
    Route::post('/coursework/{schedule}', [App\Http\Controllers\Teacher\CourseworkController::class, 'store'])->name('teacher.coursework.store');
    Route::put('/coursework/{schedule}', [App\Http\Controllers\Teacher\CourseworkController::class, 'update'])->name('teacher.coursework.update');
    Route::post('/coursework/{schedule}/toggle-status', [App\Http\Controllers\Teacher\CourseworkController::class, 'toggleStatus'])->name('teacher.coursework.toggle-status');
    Route::get('/coursework/submission/{submission}', [App\Http\Controllers\Teacher\CourseworkController::class, 'showSubmission'])->name('teacher.coursework.submission');
    Route::post('/coursework/submission/{submission}/check', [App\Http\Controllers\Teacher\CourseworkController::class, 'checkSubmission'])->name('teacher.coursework.check');
    
    // Мои студенты
    Route::get('/students', [App\Http\Controllers\Teacher\StudentController::class, 'index'])->name('teacher.students.index');
    Route::get('/students/group/{group}', [App\Http\Controllers\Teacher\StudentController::class, 'show'])->name('teacher.students.group');
    Route::get('/students/student/{student}', [App\Http\Controllers\Teacher\StudentController::class, 'showStudent'])->name('teacher.students.student');
    
    // Расписание
    Route::get('/schedule', [App\Http\Controllers\Teacher\ScheduleController::class, 'index'])->name('teacher.schedule.index');
    Route::post('/schedule', [App\Http\Controllers\Teacher\ScheduleController::class, 'store'])->name('teacher.schedule.store');
    Route::get('/schedule/{schedule}', [App\Http\Controllers\Teacher\ScheduleController::class, 'show'])->name('teacher.schedule.show');
    Route::put('/schedule/{schedule}', [App\Http\Controllers\Teacher\ScheduleController::class, 'update'])->name('teacher.schedule.update');
    Route::post('/schedule/{schedule}/toggle-status', [App\Http\Controllers\Teacher\ScheduleController::class, 'toggleStatus'])->name('teacher.schedule.toggle-status');
    Route::delete('/schedule/{schedule}', [App\Http\Controllers\Teacher\ScheduleController::class, 'destroy'])->name('teacher.schedule.destroy');
    
    // Управление силлабусами в расписании
    Route::post('/schedule/{schedule}/syllabuses', [App\Http\Controllers\Teacher\ScheduleController::class, 'addSyllabus'])->name('teacher.schedule.add-syllabus');
    Route::post('/schedule/{schedule}/syllabuses/upload', [App\Http\Controllers\Teacher\ScheduleController::class, 'uploadSyllabus'])->name('teacher.schedule.upload-syllabus');
    Route::delete('/schedule/{schedule}/syllabuses/{syllabus}', [App\Http\Controllers\Teacher\ScheduleController::class, 'removeSyllabus'])->name('teacher.schedule.remove-syllabus');
    Route::post('/schedule/{schedule}/methodical', [App\Http\Controllers\Teacher\ScheduleController::class, 'uploadMethodical'])->name('teacher.schedule.methodical.upload');
    Route::delete('/schedule/{schedule}/methodical', [App\Http\Controllers\Teacher\ScheduleController::class, 'deleteMethodical'])->name('teacher.schedule.methodical.delete');
    Route::get('/schedule/{schedule}/methodical', [App\Http\Controllers\Teacher\ScheduleController::class, 'downloadMethodical'])->name('teacher.schedule.methodical.download');
    
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
        return spa('Teacher/Materials/Index');
    })->name('teacher.materials.index');
    
    Route::get('/materials/create', function () {
        return spa('Teacher/Materials/Create');
    })->name('teacher.materials.create');
    
    // Отчеты
    Route::get('/reports', function () {
        return spa('Teacher/Reports/Index');
    })->name('teacher.reports.index');
    
    // Профиль
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('teacher.profile.index');
    Route::post('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('teacher.profile.password');
    
    // Настройки
    Route::get('/settings', function () {
        return spa('Teacher/Settings/Index');
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

require __DIR__.'/org_panels.php';

// Тестовые маршруты для SMS (удалить в production или защитить middleware)
Route::prefix('sms-test')->middleware('auth')->group(function () {
    Route::post('/send', [App\Http\Controllers\SmsTestController::class, 'send'])->name('sms.test.send');
    Route::post('/send-verification', [App\Http\Controllers\SmsTestController::class, 'sendVerificationCode'])->name('sms.test.verification');
    Route::get('/check-config', [App\Http\Controllers\SmsTestController::class, 'checkConfig'])->name('sms.test.config');
});

// Оболочка Vue для адресов без отдельного файлового ответа.
Route::get('{any}', function () {
    return view('app');
})->where('any', '^(?!api(?:/|$)).*$')->name('spa.shell');
