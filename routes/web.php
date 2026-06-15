<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Anasayfa
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return redirect('/admin/dashboard');
        } elseif ($user->isCoach()) {
            return redirect('/coach/dashboard');
        } elseif ($user->isStudent()) {
            return redirect('/student/dashboard');
        }
    }
    return view('welcome');
});

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Admin Panel Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    Route::get('/coaches', function () {
        return view('admin.coaches');
    })->name('admin.coaches');
    
    Route::get('/fields', function () {
        return view('admin.fields');
    })->name('admin.fields');
    
    Route::get('/subscriptions', function () {
        return view('admin.subscriptions');
    })->name('admin.subscriptions');
    
    Route::get('/resources', function () {
        return view('admin.resources');
    })->name('admin.resources');
});

// Coach Panel Routes
Route::prefix('coach')->middleware(['auth', 'coach'])->group(function () {
    Route::get('/dashboard', function () {
        return view('coach.dashboard');
    })->name('coach.dashboard');
    
    Route::get('/students', function () {
        return view('coach.students');
    })->name('coach.students');
    
    Route::get('/student/{student}', function ($student) {
        return view('coach.student-detail', ['studentId' => $student]);
    })->name('coach.student.detail');
    
    Route::get('/student/{student}/assign', function ($student) {
        return view('coach.assign', ['studentId' => $student]);
    })->name('coach.assign');
    
    Route::get('/student/{student}/progress', function ($student) {
        return view('coach.progress', ['studentId' => $student]);
    })->name('coach.progress');
    
    Route::get('/schedules', function () {
        return view('coach.schedules');
    })->name('coach.schedules');
    
    Route::get('/schedules/create', function () {
        return view('coach.schedule-builder');
    })->name('coach.schedules.create');
    
    Route::get('/schedules/{schedule}/edit', function ($schedule) {
        return view('coach.schedule-builder', ['scheduleId' => $schedule]);
    })->name('coach.schedules.edit');
    
    Route::get('/fields', function () {
        return view('coach.fields');
    })->name('coach.fields');
    
    Route::get('/resources', function () {
        return view('coach.resources');
    })->name('coach.resources');
    
    Route::get('/resource-assignment', function () {
        return view('coach.resource-assignment');
    })->name('coach.resource.assignment');
    
    Route::get('/questions', function () {
        return view('coach.questions');
    })->name('coach.questions');
    
    Route::get('/exams', function () {
        return view('coach.exams');
    })->name('coach.exams');
});

// Student Panel Routes
Route::prefix('student')->middleware(['auth', 'student'])->group(function () {
    Route::get('/dashboard', function () {
        return view('student.dashboard');
    })->name('student.dashboard');
    
    Route::get('/questions', function () {
        return view('student.questions');
    })->name('student.questions');
    
    Route::get('/exams', function () {
        return view('student.exams');
    })->name('student.exams');
    
    Route::get('/study', function () {
        return view('student.study');
    })->name('student.study');
    
    Route::get('/progress', function () {
        return view('student.progress');
    })->name('student.progress');
    
    Route::get('/courses', function () {
        return view('student.courses');
    })->name('student.courses');
    
    Route::get('/schedule', function () {
        return view('student.schedule');
    })->name('student.schedule');
    
    Route::get('/resources', function () {
        return view('student.resources');
    })->name('student.resources');
});
