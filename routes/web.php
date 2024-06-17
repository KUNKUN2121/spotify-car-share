<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\LyricsController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SpotifyController;
use SpotifyLyricsApi\Spotify;

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

// Route::get('/', function () {
//     return view('panels.index');
// });

Route::get('/', [RoomController::class, 'index']);
Route::get('/api/now', [RoomController::class, 'getRoomNow']);
Route::get('/api/room_queue_list', [RoomController::class, 'getRoomQueueList']);

// 曲の検索
Route::get('/api/search', [SearchController::class, 'getSearchList']);
// 曲の追加
Route::post('/api/add', [QueueController::class, 'addQueueApi']);
//csrf
Route::get('/api/csrf-token', function() {
    return response()->json(['token' => csrf_token()]);
});
// 再生履歴の取得
Route::get('/api/history', [HistoryController::class, 'getHistory'])->name('getHistory');

// Route::get('/spotify', [SpotifyController::class, 'index']);

// ログイン機能
Route::get('/admin/login', [SpotifyController::class, 'redirectToSpotify'])->name('admin.login');
Route::get('/admin/callback', [SpotifyController::class, 'handleCallback']);

Route::group(['middleware' => 'auth'], function () {
    //この中に以前の記事で書いたルーティングのコードを書いていく
    Route::get('/admin', [SpotifyController::class, 'admin']);
    Route::get('/admin/create', [RoomController::class, 'create']);
  });


// Route::get('/spotify/play', [SpotifyController::class, 'playPause']);
// Route::get('/spotify/getCurrentTrack', [SpotifyController::class, 'getCurrentTrack']);



// Route::get('/spotify/now', [SpotifyController::class, 'getNow']);
// Route::get('/spotify/lyrics', [LyricsController::class, 'index']);


// Route::get('/home', [SpotifyController::class, 'getAccessToken'])->name('getAccessToken');

// getQueueList
Route::get('/test', [SpotifyController::class, 'getCsrf'])->name('getCsrf');

Route::get('/test2', [HistoryController::class, 'getHistory'])->name('getHistory');

Route::get('/testdayo', [HistoryController::class, 'getHistory'])->name('getHistory');

