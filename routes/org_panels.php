<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

$redirectCopy = function (string $from, string $to) {
    Route::any('/'.trim($from, '/').'/{rest?}', function (?string $rest = null) use ($to) {
        $target = rtrim($to, '/');
        if ($rest !== null && $rest !== '') {
            $target .= '/'.$rest;
        }
        $query = request()->getQueryString();

        return redirect($target.($query ? '?'.$query : ''));
    })->where('rest', '.*');
};

$teachingRedirects = function () use ($redirectCopy) {
    $redirectCopy('lessons', '/teacher/lessons');
    $redirectCopy('tests', '/teacher/tests');
    $redirectCopy('grades', '/teacher/grades');
    $redirectCopy('students', '/teacher/students');
    $redirectCopy('schedule', '/teacher/schedule');
    $redirectCopy('syllabuses', '/teacher/syllabuses');
    $redirectCopy('ssr', '/teacher/ssr');
    $redirectCopy('coursework', '/teacher/coursework');
    $redirectCopy('chat', '/teacher/chat');
    $redirectCopy('materials', '/teacher/materials');
    $redirectCopy('reports', '/teacher/reports');
    $redirectCopy('schedules', '/admin/schedules');
};

Route::prefix('education')->middleware(['auth', 'staff', 'check.password.change'])->group(function () use ($redirectCopy, $teachingRedirects) {
    Route::get('/', [App\Http\Controllers\EducationDepartmentController::class, 'dashboard'])->name('education.dashboard');
    Route::get('/dashboard', [App\Http\Controllers\EducationDepartmentController::class, 'dashboard'])->name('education.dashboard.alternative');

    Route::get('/groups', [App\Http\Controllers\EducationDepartmentController::class, 'groups'])->name('education.groups.index');
    Route::get('/groups/create', [App\Http\Controllers\EducationDepartmentController::class, 'createGroup'])->name('education.groups.create');
    Route::post('/groups', [App\Http\Controllers\EducationDepartmentController::class, 'storeGroup'])->name('education.groups.store');
    Route::get('/groups/{group}/edit', [App\Http\Controllers\EducationDepartmentController::class, 'editGroup'])->name('education.groups.edit');
    Route::put('/groups/{group}', [App\Http\Controllers\EducationDepartmentController::class, 'updateGroup'])->name('education.groups.update');
    Route::post('/groups/{group}/toggle-status', [App\Http\Controllers\EducationDepartmentController::class, 'toggleGroupStatus'])->name('education.groups.toggle-status');

    Route::get('/specialties', [App\Http\Controllers\EducationDepartmentController::class, 'specialties'])->name('education.specialties.index');
    Route::get('/specialties/create', [App\Http\Controllers\EducationDepartmentController::class, 'createSpecialty'])->name('education.specialties.create');
    Route::post('/specialties', [App\Http\Controllers\EducationDepartmentController::class, 'storeSpecialty'])->name('education.specialties.store');
    Route::get('/specialties/{specialty}/edit', [App\Http\Controllers\EducationDepartmentController::class, 'editSpecialty'])->name('education.specialties.edit');
    Route::put('/specialties/{specialty}', [App\Http\Controllers\EducationDepartmentController::class, 'updateSpecialty'])->name('education.specialties.update');
    Route::post('/specialties/{specialty}/toggle-status', [App\Http\Controllers\EducationDepartmentController::class, 'toggleSpecialtyStatus'])->name('education.specialties.toggle-status');

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('education.profile.index');
    Route::post('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('education.profile.password');

    $redirectCopy('users', '/admin/users');
    $redirectCopy('subjects', '/admin/subjects');
    $redirectCopy('departments', '/admin/departments');
    $teachingRedirects();
});

Route::prefix('registration')->middleware(['auth', 'staff', 'check.password.change'])->group(function () use ($redirectCopy, $teachingRedirects) {
    Route::get('/', [App\Http\Controllers\EducationDepartmentController::class, 'dashboard'])->name('registration.dashboard');
    Route::get('/dashboard', [App\Http\Controllers\EducationDepartmentController::class, 'dashboard'])->name('registration.dashboard.alternative');

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('registration.profile.index');
    Route::post('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('registration.profile.password');

    $redirectCopy('users', '/admin/users');
    $redirectCopy('groups', '/education/groups');
    $redirectCopy('subjects', '/admin/subjects');
    $redirectCopy('departments', '/admin/departments');
    $redirectCopy('specialties', '/education/specialties');
    $teachingRedirects();
});

$registerOrgHome = function (string $prefix) use ($redirectCopy, $teachingRedirects) {
    Route::prefix($prefix)->middleware(['auth', 'staff', 'check.password.change'])->group(function () use ($prefix, $redirectCopy, $teachingRedirects) {
        Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name($prefix.'.logout');
        Route::get('/', function () {
            return spa('Teacher/Dashboard');
        })->name($prefix.'.dashboard');

        Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name($prefix.'.profile.index');
        Route::post('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name($prefix.'.profile.password');

        $redirectCopy('users', '/admin/users');
        $teachingRedirects();
    });
};

$registerOrgHome('department');
$registerOrgHome('faculty');
