<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
     * 仮想リストに追加する関数
     */

    // public function addQueueList($roomId, $MusicId){
    public function addUserQueueList(Request $request){
        $roomId = $request->input('room_id');
        $musicId = $request->input('music_id');

        $array = $this->getUserQueueList($roomId);

        $addArray = [
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
    public function addQueueApi(){

    }

    public function getQueueApi($roomId){

    }
}
