<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LyricsController;
use App\Http\Controllers\SpotifyController;
use App\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;

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
Route::get('/spotify/login', [SpotifyController::class, 'redirectToSpotify']);
Route::get('/spotify/callback', [SpotifyController::class, 'handleCallback']);

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
