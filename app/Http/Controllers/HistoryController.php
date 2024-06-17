<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class HistoryController extends Controller
{
    public function getHistory(Request $request){
        $roomCtr = new RoomController();
        $sptCtr = new SpotifyController();
        $roomId = $request->input('room_id');
        $ownerToken = $roomCtr->getRoomOwnerToken($roomId);
        $url = "/v1/me/player/recently-played";
        $value = $sptCtr->getApi($ownerToken,$url );
        $item = $value['result'];

        // for($i=0; $i < count($item); $i++){

        //     echo $item[$i]['track']['name'];
        //     echo '<br/>';
        // }
        // for($)
        // TODO : 取得できなかったときの処理
        return response()->json($item, 200, array('Access-Control-Allow-Origin' => '*'));;
    }
}
