<?php

use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, "redirectToProvider"]);
Route::get('/auth/{provider}/callback', [SocialAuthController::class, "handleProviderCallback"]);
Route::view('/login', 'login')->name('login');
