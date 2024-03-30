<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Models\Token;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
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

    // 初回ログイン
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


            // DB 処理
            $userToken = Token::find(1);
            $userToken->update([
                "token" => $access_token,
                "refresh_token" => $refresh_token,
                "token_at" => Carbon::now(),
                "refresh_token_at" => Carbon::now(),
            ]);




            // cache(['access_token' => $access_token], now()->addMinutes(60));
            cache(['access_token' => $access_token]);
            cache(['refresh_token' => $refresh_token], now()->addDays(6));
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
        $cached_refresh_token = cache('refresh_token');

        // DB 処理
        $userToken = Token::find(1);
        $cached_refresh_token = $userToken->refresh_token;


        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $cached_refresh_token,
            'client_id' => config('services.spotify.client_id'),
            'client_secret' => config('services.spotify.client_secret'),
        ]);
        $access_token = $response['access_token'];

        //DB処理
        $userToken = $userToken->update([
            "token" => $access_token,
        ]);

        cache(['access_token' => $access_token], now()->addMinutes(59));
    }

    public function playPause(){

        $cached_access_token = cache('access_token');
        $cached_refresh_token = cache('refresh_token');

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
        // return view('home')->with($value);
    }



    public function getNow(){
        // 2秒ごとに更新する。

        $value = Cache::remember('getNow', 3, function () {
            //DB処理
            $userToken = Token::find(1);
            $cachedAccessToken = $userToken->token;

            $result = SpotifyController::getApi($cachedAccessToken, '/v1/me/player/currently-playing')['result'];
            // dd($getApi);
            if(!$result == null){

                if(cache('old_title') != $result['item']['name']){

                    $lyricsCon = new LyricsController;
                    // キャッシュを更新する
                    cache(['old_title' => $result['item']['name']]);
                    cache(['lyrics' => $lyricsCon->get($result['item']['id'])]);

                    // $oldTitle = $result['item']['name'];
                }

                $musicInfo = [
                    'is_playing' =>  $result['is_playing'],
                    'title' =>  $result['item']['name'],
                    'artist' => $result['item']['artists'],
                    'album' => $result['item']['album']['name'],
                    'duration_ms' => $result['item']['duration_ms'],
                    'progress_ms' => $result['progress_ms'],
                    'links' => [
                        'album-art' => $result['item']['album']['images'][0]['url'],
                        'song-id' => $result['item']['id'],
                        'song-url' => $result['item']['external_urls']['spotify'],
                        'album' => 'https"//albumURL',
                        'artist' => 'artistURL',

                    ],
                    'lyrics' => cache('lyrics'),
                    // 'get_timestamp' => $result['timestamp'],
                    'get_spotify_timestamp' => now(),
                ];
            }else{
                $musicInfo = null;
            }
            return $musicInfo;
        });

        if($value != []){

            $lyricsCon = new LyricsController;
            // $value['lyrics'] = $lyricsCon->get($value['item']['id']);
            $value['access_timestamp'] = now();
        }

        // return $value;
        return response()->json($value,200, array('Access-Control-Allow-Origin' => '*'));


    }


    // API取得用 240229
    public function getApi($token, $request){

        $url = "https://api.spotify.com".$request;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept-Language' =>  'ja',
        ])->get($url);

        $result = $response->json();

        if ($response->successful()) {

            return [
                'response' => $response,
                'result' => $result
            ];
        }else if ($response->unauthorized()){
            // 認証エラー
            $this->refreshAccessToken();
        }else {
            return null;
        }

        if($result == null){
            return response()->json(['error' => '現在再生していません。', 'details' => $result], $response->status());
        }
    }


}
