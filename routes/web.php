<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\LyricsController;
use App\Http\Controllers\SpotifyController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/spotify', [SpotifyController::class, 'index']);
// Route::get('/admin/login', [SpotifyController::class, 'redirectToSpotify']);
Route::get('/admin/login', function () {
    return Socialite::driver('spotify')->redirect();
});



Route::get('/admin/callback', [SpotifyController::class, 'handleCallback2']);
Route::get('/admin', [SpotifyController::class, 'admin']);

Route::get('/spotify/play', [SpotifyController::class, 'playPause']);
Route::get('/spotify/getCurrentTrack', [SpotifyController::class, 'getCurrentTrack']);



Route::get('/spotify/now', [SpotifyController::class, 'getNow']);
Route::get('/spotify/lyrics', [LyricsController::class, 'index']);


Route::get('/home', [SpotifyController::class, 'getAccessToken'])->name('getAccessToken');


Route::get('/test', function () {
    return view('test-vue');
});


Route::get('/spotify/admin', [AdminController::class, 'index']);
Route::get('/spotify/token', [TokenController::class, 'index']);
