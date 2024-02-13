let param;
function getLyrics(title, artist){
    param = {
        track_name : title,
        artist_name : artist,
    }
    loadLyrics();

}




let lyricsData = [];

// LRCファイルを読み込み、解析するための関数
function loadLyrics() {
        $.ajax({
            url: 'https://lrclib.net/api/search',
            type: 'GET',
            data: param,
            success: function(data) {
                if(data.length == 0) return;
                if (data[0]['syncedLyrics'] !== null) lyricsData = parseLRC(data[0]['syncedLyrics']);
                else{
                    lyricsData = parseLRC(data[0]['plainLyrics']);
            }
                updateLyrics();
    },
    error: function (error) {
      console.error('歌詞の読み込みエラー:', error);
    },
  });
}

// LRCデータを解析するための関数
function parseLRC(data) {
    const lines = data.split('\n');
    const result = [];


    for (const line of lines) {
      const match = line.match(/\[(\d+:\d+\.\d+)\](.+)/);
      if (match) {
        const timestamp = match[1];
        const text = match[2];
        result.push({ timestamp, text });
      }
      else{
        console.log(line);
        result.push({timestamp: null, text: line });
      }
    }

    return result;
  }

  // 歌詞の表示を更新するための関数
//   function updateLyrics() {
//     const lyricsArea = document.querySelector("#lyricsArea");
//     lyricsArea.innerHTML = '';

//     for (const line of lyricsData) {
//       const paragraph = document.createElement('p');
//       paragraph.textContent = line.text;
//       lyricsArea.appendChild(paragraph);
//     }
//   }

function updateLyrics() {
    const lyricsArea = document.querySelector("#lyricsArea");
    lyricsArea.innerHTML = '';

    const currentTime = convertTime(progressMs); // 現在の再生位置を取得

    for (const line of lyricsData) {
      const paragraph = document.createElement('p');
      paragraph.textContent = line.text;

      // タイムスタンプを秒数に変換して比較
      if(line.timestamp !== null){
        const timestampInSeconds = convertTimestampToSeconds(line.timestamp);
        const currentTimeInSeconds = convertTimestampToSeconds(currentTime);

        // 現在の再生位置に対応する行を太字にする
        if (currentTimeInSeconds >= timestampInSeconds) {
          paragraph.style.fontWeight = 'bold';
        }
      }


      lyricsArea.appendChild(paragraph);
    }
  }

  // タイムスタンプを秒数に変換する関数
    function convertTimestampToSeconds(timestamp) {
    const [minutes, seconds] = timestamp.split(':').map(parseFloat);
    return minutes * 60 + seconds;
    }



  // ページが読み込まれたときにloadLyrics関数を呼び出す
  $(document).ready(function () {
    // loadLyrics();
  });
