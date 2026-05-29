<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Pantau Pangan
|--------------------------------------------------------------------------
| All pages are SPA-like: Blade renders the shell, JavaScript fetches
| data from the REST API using JWT stored in localStorage.
*/

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/register/merchant', function () {
    return view('auth.register-merchant');
})->name('register.merchant');

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');

Route::get('/products', function () {
    return view('products.index');
})->name('products');

Route::get('/products/{slug}', function ($slug) {
    return view('products.show', ['slug' => $slug]);
})->name('products.show');

Route::get('/merchant', function () {
    return view('merchant.dashboard');
})->name('merchant.dashboard');

Route::get('/admin', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/admin/merchants', function () {
    return view('admin.merchants');
})->name('admin.merchants');

Route::get('/admin/users', function () {
    return view('admin.users');
})->name('admin.users');

Route::get('/admin/prices', function () {
    return view('admin.prices');
})->name('admin.prices');

Route::get('/admin/reports', function () {
    return view('admin.reports');
})->name('admin.reports');

Route::get('/news', function () {
    return view('news.index');
})->name('news');

// Static Pages
Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/privacy', function () {
    return view('pages.privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('pages.terms');
})->name('terms');
