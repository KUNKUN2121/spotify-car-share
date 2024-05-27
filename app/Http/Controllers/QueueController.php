<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SpotifyController;

class QueueController extends Controller
{
    /**
     * 仮想リストを取得する関数
     */
    public function getUserQueueList($roomId){
        $array = cache($roomId.'_queue_list');
        if($array == null) $array = [];
        return $array;
    }

    /**
     * 仮想リストをPOPする関数
     */
    public function getPopUserQueueList($roomId){
        $array = cache($roomId.'_queue_list');
        if($array == null) return [];
        $popItem = array_shift($array);
        Cache::put($roomId.'_queue_list', $array, 6000);
        return $popItem;
    }

    /**
     * 仮想リストに追加する関数
     */

    // public function addQueueList($roomId, $MusicId){
    public function addUserQueueList(Request $request){
        $roomId = $request->input('room_id');
        $musicId = $request->input('music_id');

        $array = $this->getUserQueueList($roomId);
        if($musicId == null ){
            $array = [];
            Cache::put($roomId.'_queue_list', $array, 6000);
            return 404;
        }
        $addArray = [
            // 'music_id' => "7KExqPOvjFzAI4d49mQxt9",
            'music_id' => $musicId,
            'add_userId' => 'test',
            'is_add' => false,
        ];
        $array[] = ($addArray);
        Cache::put($roomId.'_queue_list', $array, 6000);
        dd($array);
    }


    /**
     * SpotifyAPIに追加する関数
     */
    public function addQueueApi($roomId ,$musicId){
    // public function addQueueApi(Request $request){
        // $roomId = $request->input('room_id');
        $roomCtr = new RoomController();
        $sptCtr = new SpotifyController();
        $ownerToken = $roomCtr->gerRoomOwnerToken($roomId);
        $uri = "spotify:track:" . $musicId;
        $value = $sptCtr->postApi($ownerToken, "/v1/me/player/queue" . "?uri=" . $uri);
    }

    public function getQueueApi($roomId){
    // public function getQueueApi(Request $request){
        // $roomId = $request->input('room_id');
        $roomCtr = new RoomController();
        $sptCtr = new SpotifyController();
        $ownerToken = $roomCtr->gerRoomOwnerToken($roomId);
        $value = $sptCtr->getApi($ownerToken, "/v1/me/player/queue");
        return $value;
    }

    /**
     * 比較して追加する。
     */
    public function checkAdd($roomId, $nowSongId){
    // public function checkAdd(Request $request){
        // $roomId = $request->input('room_id');
        $sptQueList = $this->getQueueApi($roomId);

        $virQueList = $this->getUserQueueList($roomId);

        if(isset($virQueList[0]['music_id'])){
            if($virQueList[0]['music_id'] == $nowSongId){
                // 仮想配列を削除する。
                $popItem = $this->getPopUserQueueList($roomId);
            }
            if($sptQueList['result']['queue'][0]['id'] == $virQueList[0]['music_id']){

            }else{
               // addQueueApi する。
            //    dd($sptQueList['result']['queue'][0]['uri']);
               $this->addQueueApi($roomId , $virQueList[0]['music_id']);
            }
        }
        $roomCtr = new RoomController();
        $sptCtr = new SpotifyController();
        $ownerToken = $roomCtr->gerRoomOwnerToken($roomId);

        return;

    }
}
