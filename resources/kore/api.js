let oldTitle = null;

let title;
let artist;
let albumArt;
let durationMs;
let progressMs;
let isPlaying;
function getTrackInfo() {
    $.ajax({
        url: 'http://100.97.156.8/spotify/now',
        type: 'GET',
        success: function(data) {
            if(Object.keys(data).length != 0) {
                title = data['title'];
                artist = data['artist'];
                album = data['album'];
                albumArt = data['links']['album-art'];
                durationMs = data['duration_ms'];
                progressMs = progressMsCounter(data);
                isPlaying = data['is_playing'];

                // console.log(convertMsToSec(progressMs));
                // syncedLyrics(convertMsToSec(progressMs));

                if(title != oldTitle){
                    oldTitle = title
                    songReload();
                    console.log(title);
                    
                    console.log({
                        'title' : title,
                        'artist' : artist[0]['name'],
                        'album' : album,
                        'durationMs' : durationMs/1000,

                    })
                }
            }
        },
        error: function(error) {
            console.error('Error fetching track info:', error);
        }
    });
}


function progressMsCounter(data){
    if(isPlaying != false){
        let diffSec;
        let accessTimestamp = new Date(data['access_timestamp']);
        let getSpotifyTimestamp = new Date(data['get_spotify_timestamp']);
        diffSec = accessTimestamp.getTime() - getSpotifyTimestamp.getTime();
        return  data['progress_ms'] + diffSec;
    }else{
        return data['progress_ms']
    }

}

setInterval(() => {
    getTrackInfo();
}, 1500);

setInterval(() => {
    if(isPlaying != false){
        progressMs = progressMs + 100;
        setProgressBar();
    }
    document.querySelector('.progress').innerHTML = convertMsToMimSec(progressMs)
   
    if(lyricsData != undefined){
        // console.log('歌詞ある')
        syncedLyrics(convertMsToSec(progressMs));
    }
    
}, 100);


function songReload(){
    // タイトル
    // nav right controller title
    document.querySelector('.duration').innerHTML = convertMsToMimSec(durationMs);

    // タイトル関連
    const nowPlaying = document.querySelector('.main').querySelector('.now-playing')
    const nowPlayingRight =  nowPlaying.querySelector('.right')
    const nowPlayingRightArtists = nowPlaying.querySelector('.artists')

    nowPlayingRight.querySelector('.title').innerHTML = title;

    nowPlayingRightArtists.innerHTML = '';
    artist.forEach(element => {
        const paragraph = document.createElement('p');
        paragraph.textContent = element['name'];
        // paragraph.id = line.count;
        nowPlayingRightArtists.appendChild(paragraph);
    });



    document.querySelector('.nav').querySelector('.right').querySelector('.controller').querySelector('.title').innerHTML = title;
    //アーティスト
    document.querySelector('.nav').querySelector('.right').querySelector('.controller').querySelector('.artist').innerHTML = artist[0]['name'];
    // アルバムアート
    document.querySelector('.album-art').src = albumArt;;
    // 歌詞読み込み
    loadLyrics({ track_name : title , artist_name : artist[0]['name']})

}


function convertMsToMimSec(value){
    let seconds = Math.floor(value / 1000);
    let minutes = Math.floor(seconds / 60);
    seconds = seconds % 60;
    minutes = minutes.toString().padStart(2, '0');
    seconds = seconds.toString().padStart(2, '0');
    // '${minutes}:{$seconds}'
    return `${minutes}:${seconds}`;

}


function setProgressBar(){
    document.getElementsByClassName("progress-bar")[0].style.backgroundSize = '0%'
    let progressBar = document.querySelector(".progress-bar");
    let progressBarPersent = (progressMs / durationMs) * 100 ;

    progressBar.style.backgroundSize = progressBarPersent + '%';
}