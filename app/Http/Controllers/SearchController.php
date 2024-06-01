<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function getSearchList(Request $request){
        $roomId = $request->input('room_id');
        $searchWord = $request->input('q');
        // $roomId = "xRouwJ6jx51gv0WPNdWv1kpcaFO5La4d";
        // $searchWord = "ワタリドリ";

        $roomCtr = new RoomController();
        $sptCtr = new SpotifyController();
        $ownerToken = $roomCtr->getRoomOwnerToken($roomId);
        $value = $sptCtr->getApi($ownerToken, "/v1/search?q=". $searchWord ."&type=track");
        // dd($value);
        return response()->json($value,200, array('Access-Control-Allow-Origin' => '*'));
    }
}
