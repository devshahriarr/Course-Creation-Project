<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseCategoryController;
use App\Http\Controllers\CourseController;

Route::get('/', [CourseController::class, 'create'])->name('courses.create');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index'); // JSON
Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
Route::post('/categories', [CourseCategoryController::class, 'storeCategory'])->name('categories.store');
