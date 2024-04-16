<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class RoomController extends Controller
{
    public function index(Request $request){

        $roomId = $request->input('room_id');
        $result = RoomController::getRoomGetNow($roomId);
        return response()->json($result,200, array('Access-Control-Allow-Origin' => '*'));


    }

    public function getRoomGetNow($roomId){
        // ルームとオーナーを取得
        $room = Room::where('room_id', $roomId)->firstOrFail();
        $owner = User::where('spotify_id', $room->spotify_id)->firstOrFail();
        $ownerToken = $owner->token;

        // SpotifyControllerを使用して現在の情報を取得
        $result = $this->getSpotifyResult($ownerToken,$roomId);

        // 401エラーの場合はトークンを更新して再度取得を試みる
        if ($result === 401) {
            $this->refreshAndRetry($owner->spotify_id, $roomId);
        }

        // 異なる曲が再生された場合は歌詞を取得する
        $this->checkAndFetchLyrics($roomId, $result);

        // 結果を返す
        return $result;
    }

    private function getSpotifyResult($ownerToken, $roomId) {
        return Cache::remember($roomId, 1, function () use ($ownerToken) {
            $spotifyController = new SpotifyController;
            return $spotifyController->getNow($ownerToken);
        });
    }

    private function refreshAndRetry($ownerId, $roomId) {
        $spotifyController = new SpotifyController;
        $spotifyController->refreshAccessToken($ownerId);
        return $this->getRoomGetNow($roomId);
    }

    private function checkAndFetchLyrics($roomId, &$result) {
        if(isset($result['links']['song-id'])){

            $cacheResult = cache($roomId.'_result');

            if(isset($cacheResult['links']['song-id'])){
                if($result['links']['song-id'] != $cacheResult['links']['song-id'] || !isset($cacheResult['lyrics'])){
                    // songIdが変わった場合の処理
                    // echo '実行しました。';
                    $LyricsController = new LyricsController;
                    $result['lyrics'] = $LyricsController->get($result['links']['song-id']);
                } else {
                    // 歌詞がキャッシュされている場合はそれを使用する
                    if(isset($cacheResult['lyrics'])) {
                        $result['lyrics'] = $cacheResult['lyrics'];
                    }
                }
            }else{
                // echo 'キャッシュなし実行しました。2';
                $LyricsController = new LyricsController;
                $result['lyrics'] = $LyricsController->get($result['links']['song-id']);
            }
            // 結果をキャッシュする
            cache([$roomId.'_result' => $result], 300);
        }
    }

}
