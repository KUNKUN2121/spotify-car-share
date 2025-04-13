<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LyricsNewController extends Controller
{
    //
    public function get($data){
        // dd($data);
        // dd($data['duration_ms']);

        $api_url = "https://lrclib.net/api/search";
        $track_name = $data['title'];
        // $artist_name = $data['artists'][0]['name'];
        $artist_name = "";

        $duration_ms = $data['duration_ms'];
        $duration = $duration_ms / 1000;


        $url = $api_url . "?track_name=" . urlencode($track_name) . "&artist_name=" . urlencode($artist_name);
        $response = file_get_contents($url);
        $data = json_decode($response, true);


        // dd($data[0]['syncedLyrics']);
        $result = [];
        if(empty($data)) {
            $result['response'] = 404;
            return $result;
        }


        // durationの誤差が3秒以内のものを取得
        $durationany = [];
        foreach ($data as $item) {
            $item_duration = $item['duration'];
            if (abs($item_duration - $duration) <= 3) {
                $durationany[] = $item;
            }
        }
        // その中で、SyncedLyricsがあるものを上にソート
        usort($durationany, function($a, $b) {
            if ($a['syncedLyrics'] === null && $b['syncedLyrics'] !== null) {
                return 1;
            } elseif ($a['syncedLyrics'] !== null && $b['syncedLyrics'] === null) {
                return -1;
            } else {
                return 0;
            }
        });




        $selectedData = $durationany[0];


        if($selectedData["syncedLyrics"] === null) {
            $result['response'] = 201;
            $result['syncedLyrics'] = $selectedData['plainLyrics'];
            return $result;
        }

        // dd($data[0]['syncedLyrics']);


        $result['response'] = 200;
        $result['syncedLyrics'] = $selectedData['syncedLyrics'];
        return $result;

    }
}
