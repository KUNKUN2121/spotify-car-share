<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Models\Token;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Laravel\Socialite\Facades\Socialite;

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


    // 初回ログイン
    public function redirectToSpotify()
    {
        $scopes = 'user-read-private user-read-email user-modify-playback-state user-read-currently-playing user-read-playback-state' ; // 必要なスコープを指定
        $url = "https://accounts.spotify.com/authorize?client_id=" . config('services.spotify.client_id') . "&response_type=code&redirect_uri=" . config('services.spotify.redirect_uri') . "&scope=" . $scopes;
        return redirect($url);
    }

    //
    public function handleCallback(Request $request)
    {
        // ログイン情報を取得する
        $code = $request->input('code');
        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => config('services.spotify.redirect_uri'),
            'client_id' => config('services.spotify.client_id'),
            'client_secret' => config('services.spotify.client_secret'),
        ]);
        try{
            $access_token = $response['access_token'];
            $refresh_token = $response['refresh_token'];
            $result = SpotifyController::getApi($access_token, "/v1/me");
            $resultUserId = $result['result']['id'];
            //code...
        } catch (\Throwable $th) {
            throw $th;
        }

        // SpotifyのユーザIDが token に存在するかを確認する
        $user = User::where('spotify_id', $resultUserId)->first();
        if($user == null){
            $user = User::create([
                'spotify_id'=> $resultUserId,
                'token' => $access_token,
                'refresh_token' => $refresh_token,
            ]);
        }

        Auth::login($user);
        return redirect('/admin');
    }

    public function admin(Request $request){
        // Auth::user();
        return view('admins.index');
    }

    // リフレッシュアクセストークン
    public function refreshAccessToken($userId)
    {
        // USERID から 取得して いれる。
        // DB 処理
        $user = User::where('spotify_id' , $userId)->first();


        $refresh_token = $user->refresh_token;


        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id' => config('services.spotify.client_id'),
            'client_secret' => config('services.spotify.client_secret'),
        ]);
        $access_token = $response['access_token'];

        //DB処理
        $userToken = $user->update([
            "token" => $access_token,
        ]);


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



    public function getNow($accessToken){
        // 2秒ごとに更新する。
            $value = SpotifyController::getApi($accessToken, '/v1/me/player/currently-playing');

            if($value == 401){
                return 401;
            }

            $result = $value['result'];
            if(!$result == null){
                $musicInfo = [
                    'is_playing' =>  $result['is_playing'],
                    'title' =>  $result['item']['name'],
                    'artists' => $result['item']['artists'],
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

                    // 'get_timestamp' => $result['timestamp'],
                    'get_spotify_timestamp' => now(),
                ];
            }else{
                $musicInfo = null;
            }

        if($musicInfo != []){
            $value['access_timestamp'] = now();
        }
        return $musicInfo;
        // return $value;
        // return response()->json($value,200, array('Access-Control-Allow-Origin' => '*'));



    }


    public function getQueueList($accessToken, $num){
        $result = $this->getApi($accessToken, "/v1/me/player/queue");
        if($result == 401){
            return 401;
        }
        $queueList =$result['result']['queue'];
        // dd($result['result']);
        if($result['result']['queue'] == []){
            return [];
        }
        for($i=0; $i < $num; $i++){
            $value['queue'][$i] = $queueList[$i];
        }
        // $result['get_spotify_timestamp'] = now();
        return $value;

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
            // $this->refreshAccessToken();
            return 401;
        }else {
            return null;
        }

        if($result == null){
            return response()->json(['error' => '現在再生していません。', 'details' => $result], $response->status());
        }
    }


}
