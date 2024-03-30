<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use SpotifyLyricsApi;

class LyricsController extends Controller
{
    //
    function index(Request $request){
        $trackid = $request->input('trackid') ?? null;
        $url = $_GET[ 'url' ] ?? null;
        $format = $request->input('format') ?? null;

        $re = '~[\bhttps://open.\b]*spotify[\b.com\b]*[/:]*track[/:]*([A-Za-z0-9]+)~';

        if ( !$trackid && !$url ) {
            http_response_code( 400 );
            $reponse = json_encode( [ 'error' => true, 'message' => 'url or trackid parameter is required!', 'usage' => 'https://github.com/akashrchandran/spotify-lyrics-api' ] );
            echo $reponse;
            return;
        }
        if ( $url ) {
            preg_match( $re, $url, $matches, PREG_OFFSET_CAPTURE, 0 );
            $trackid = $matches[ 1 ][ 0 ];
        }
        $spotify = new SpotifyLyricsApi\Spotify( config('services.spotify.sp_dc') );

        $spotify->checkTokenExpire();
        $reponse = $spotify->getLyrics( track_id: $trackid );
        $value =  $this->make_response( $spotify, $reponse, $format );
        return response()->json($value,200, array('Access-Control-Allow-Origin' => '*'));

    }


    function get(String $trackId){
        $url = $_GET[ 'url' ] ?? null;
        $format = 'lrc';

        $re = '~[\bhttps://open.\b]*spotify[\b.com\b]*[/:]*track[/:]*([A-Za-z0-9]+)~';

        if ( !$trackId && !$url ) {
            http_response_code( 400 );
            $reponse = json_encode( [ 'error' => true, 'message' => 'url or trackId parameter is required!', 'usage' => 'https://github.com/akashrchandran/spotify-lyrics-api' ] );
            echo $reponse;
            return;
        }
        if ( $url ) {
            preg_match( $re, $url, $matches, PREG_OFFSET_CAPTURE, 0 );
            $trackId = $matches[ 1 ][ 0 ];
        }
        $spotify = new SpotifyLyricsApi\Spotify( config('services.spotify.sp_dc') );

        $spotify->checkTokenExpire();
        $reponse = $spotify->getLyrics( track_id: $trackId );
        $value =  $this->make_response( $spotify, $reponse, $format ,now());
        return $value;

    }



    function make_response( $spotify, $response, $format )
    {
       $json_res = json_decode( $response, true );

       if ( $json_res === null || !isset( $json_res[ 'lyrics' ] ) ) {
           http_response_code( 404 );
           return json_encode( [ 'error' => true, 'message' => 'lyrics for this track is not available on spotify!' ] );
       }
       $lines = $format == 'lrc' ? $this->getLrcLyrics( $spotify , $json_res[ 'lyrics' ][ 'lines' ] ) : $json_res[ 'lyrics' ][ 'lines' ];

       $response = [
           'error' => false,
           'syncType' => $json_res[ 'lyrics' ][ 'syncType' ],
           // 'lines' => $lines
           'syncedLyrics' => $lines,
           'timestamp' => now()
       ];
    //    return json_encode( $response );
       return $response;
   }

   function getLrcLyrics($spotify ,$lyrics): string
   {
       // $lrc = array();
       $lrc = '';
       foreach ($lyrics as $lines) {
           $lrctime = $spotify->formatMS($lines['startTimeMs']);
           // array_push($lrc, ['timeTag' => $lrctime, 'words' => $lines['words']]);
           $lrc .= "[{$lrctime}] {$lines['words']}";
           $lrc .= " \n";
       }
       return $lrc;
   }
}
