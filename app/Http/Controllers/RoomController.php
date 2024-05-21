<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
class RoomController extends Controller
{
    public function index(Request $request){



    }

    public function getRoomNow(Request $request){
        $roomId = $request->input('room_id');
        $room = Room::where('room_id', $roomId)->firstOrFail();
        $owner = User::where('spotify_id', $room->spotify_id)->firstOrFail();
        $ownerToken = $owner->token;

        // SpotifyControllerを使用して現在の情報を取得
        $result = $this->getRoomNowResult($ownerToken,$roomId);

        // 401エラーの場合はトークンを更新して再度取得を試みる
        if ($result === 401) {
            $this->refreshAndRetry($owner->spotify_id, $roomId);
            return $this->getRoomNow($request);
        }

        // 歌詞を追加する
        $this->checkAndFetchLyrics($roomId, $result);
             return response()->json($result,200, array('Access-Control-Allow-Origin' => '*'));
    }

    private function getRoomNowResult($ownerToken, $roomId) {
        // キャシュ時間変更
        return Cache::remember($roomId, 1, function () use ($ownerToken) {
            $spotifyController = new SpotifyController;
            $value = $spotifyController->getNow($ownerToken);
            $queue =$spotifyController->getQueueList($ownerToken, 3);
            if($queue == 401) return 401;
            if($queue != []) $value += $queue;
            return $value;
        });
    }

    private function refreshAndRetry($ownerId, $roomId) {
        $spotifyController = new SpotifyController;
        $spotifyController->refreshAccessToken($ownerId);
    }

    private function checkAndFetchLyrics($roomId, &$result) {
        if(isset($result['links']['song-id'])){

            $cacheResult = cache($roomId.'_result');

            if(isset($cacheResult['links']['song-id'])){
                if($result['links']['song-id'] != $cacheResult['links']['song-id'] || !isset($cacheResult['lyrics'])){
                    // songIdが変わった場合の処理
                    $LyricsController = new LyricsController;
                    $result['lyrics'] = $LyricsController->get($result['links']['song-id']);
                } else {
                    // 歌詞がキャッシュされている場合はそれを使用する
                    if(isset($cacheResult['lyrics'])) {
                        $result['lyrics'] = $cacheResult['lyrics'];
                    }
                }
            }else{
                $LyricsController = new LyricsController;
                $result['lyrics'] = $LyricsController->get($result['links']['song-id']);
            }
            // 結果をキャッシュする
            cache([$roomId.'_result' => $result], 600);
        }
    }

}
