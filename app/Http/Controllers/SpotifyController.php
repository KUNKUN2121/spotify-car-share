<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class SpotifyController extends Controller
{
    public function index(){

        // $nowPlaying = SpotifyController::getCurrentTrack();

        // if(gettype($nowPlaying) != 'array'){
        //     $nowPlaying == null;
        // }
        return view('home');
        // dd($nowPlaying);
        // return view('home')->with($nowPlaying);
    }

    public function sendSpotifyAPI($url, $header){
        // ここでアクセストークンを取得する。今後はDBからかな
        $cached_access_token = cache('access_token');
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $cached_access_token,
            'Content-Type' => 'application/json',
        // ])->put('https://api.spotify.com/v1/me/player/pause');
        ])->put("https://api.spotify.com/v1". $url );
        $result = $response->json();
        // 成功したかどうかを確認
        if ($response->successful()) {
            return $response;
            return response()->json(['message' => 'Successfull']);
        } else {
             // エラーが発生した場合Loginからやり直す。
            //  return redirect('spotify/login');
            return response()->json(['error' => 'Failed ', 'details' => $result], $response->status());
        }

    }

    // ログイン処理

    public function redirectToSpotify()
    {
        $scopes = 'user-read-private user-read-email user-modify-playback-state user-read-currently-playing' ; // 必要なスコープを指定
        $url = "https://accounts.spotify.com/authorize?client_id=" . config('services.spotify.client_id') . "&response_type=code&redirect_uri=" . config('services.spotify.redirect_uri') . "&scope=" . $scopes;
        return redirect($url);
    }

    // アクセストークン リフレッシュトークンを取得する。
    public function handleCallback(Request $request)
    {

        $code = $request->input('code');
        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => config('services.spotify.redirect_uri'),
            'client_id' => config('services.spotify.client_id'),
            'client_secret' => config('services.spotify.client_secret'),
        ]);

        try {
            $access_token = $response['access_token'];
            $refresh_token = $response['refresh_token'];

            cache(['access_token' => $access_token], now()->addMinutes(60));
            cache(['refresh_token' => $refresh_token], now()->addDays(7));
        } catch (\Throwable $th) {
            dd($th);
            // エラーが発生した場合Loginからやり直す。
            return redirect('spotify/login');
        }


        return redirect('spotify/');
    }

    // リフレッシュアクセストークン
    public function refreshAccessToken()
    {
        $session_refresh_token = session('refresh_token');

        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $session_refresh_token,
            'client_id' => config('services.spotify.client_id'),
            'client_secret' => config('services.spotify.client_secret'),
        ]);

        $access_token = $response['access_token'];
        cache(['access_token' => $access_token], now()->addMinutes(60));

        return response()->json(['message' => 'Successfully refreshed the access token']);
    }







    public function playPause(){

        $cached_access_token = cache('access_token');
        $cached_refresh_token = cache('refresh_token');

        // $response = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . $cached_access_token,
        //     'Content-Type' => 'application/json',
        // ])->put('https://api.spotify.com/v1/me/player/play');

        // $result = $response->json();

        // // 成功したかどうかを確認
        // if ($response->successful()) {
        //     return response()->json(['message' => 'Successfully played the current track']);
        // } else {
        //      // エラーが発生した場合Loginからやり直す。
        //      // return redirect('spotify/login');
        //     return response()->json(['error' => 'Failed to play the current track', 'details' => $result], $response->status());
        // }
        return SpotifyController::sendSpotifyAPI('/me/player/pause', '');
    }

    public function getCurrentTrack()
    {
        $cachedAccessToken = cache('access_token');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $cachedAccessToken,
        ])->get('https://api.spotify.com/v1/me/player/currently-playing');

        $result = $response->json();

        if (!$response->successful()) {
            // JSONで返して処理を終了する。
            // return response()->json(['error' => '現在のトラックの取得に失敗しました', 'details' => $result], $response->status());

            return null;
        }
        if($result == null){
            return response()->json(['error' => '現在再生していません。', 'details' => $result], $response->status());
        }
        $title = $result['item']['name'];
        $artist = $result['item']['artists'][0]['name'];
        $albumArt = $result['item']['album']['images'][0]['url'];
        $durationMs = $result['item']['duration_ms'];
        $progressMs = $result['progress_ms'];



        $value = compact('title', 'artist', 'albumArt', 'durationMs', 'progressMs');
        return $value;
        return view('home')->with($value);
    }


}
