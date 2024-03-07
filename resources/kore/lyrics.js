var lyricsData;
async function loadLyrics(param) {
    lyricsArea = document.querySelector(".lyrics");
    lyricsArea.innerHTML = '';
    lyricsData = null;
    nowLyricId = 0;
    progressMs = 0;

    serachFromAll()
}



function serachFromAll(){
    let result;
    $.ajax({
        url: 'https://lrclib.net/api/get',
        type: 'GET',
        data: {track_name : title , album_name : album , artist_name : artist[0]['name'], duration : durationMs/1000},
        success: function(data) {
            console.log(data);
            // 検索結果があるか
            if(data.length != 0){
                // 同期済み歌詞があるか
                if(data['syncedLyrics'] != undefined){
                    console.log('歌詞検索 : 同期歌詞あり')
                    lyricsData = parseLRC(data['syncedLyrics']);
                // 同期済みの歌詞がない
                }else{
                    // 非同期歌詞があるか
                    if(data['plainLyrics'] != undefined){
                        console.log('歌詞検索 : 非同期歌詞あり')
                        lyricsData = parseLRC(data['plainLyrics']);
                    }else{
                        console.log('歌詞検索 : 歌詞が見つかりませんでした。')
                        lyricsData = null;
                    }
                }
            }
            addLrc(lyricsData);
        },
        error: function (error) {
            if (error.status === 404) {
                lyricsData = null;
                addLrc(lyricsData);
            } else{
                console.error('歌詞検索 : 歌詞の読み込みエラー:', error);
            }
        },});
}





    // 指定した場所まで飛ばす
    function scrollToCenter(elementId) {
        var container = document.getElementsByClassName('lyrics')[0];
        var targetElement = document.getElementById(elementId);
        var targetBackElement = document.getElementById(elementId-1);
    
        if (container && targetElement) {
            var containerRect = container.getBoundingClientRect();
            var targetRect = targetElement.getBoundingClientRect();
            var offset = targetRect.top - containerRect.top - (containerRect.height - targetRect.height) / 2;
    
            container.scrollTo({
            top: container.scrollTop + offset,
            behavior: 'smooth'
            });
            console.log('スクローラー : 移動完了')
        }
        if(targetBackElement != undefined){
            targetElement.style.color = 'white';
            targetBackElement.style.color = 'black';
        }
    }
    
    // 検索
    let nowLyricId = 0;
    function syncedLyrics(sec){
        // 動機歌詞でない場合
        if(lyricsData[nowLyricId]['timestamp'] != null){
            // 現在の秒数が 現在の読み込み歌詞の以上であれば歌詞を一つ移動させる。
            if (sec >= convertTimestampToSeconds(lyricsData[nowLyricId+1].timestamp)){
                if(sec < convertTimestampToSeconds(lyricsData[nowLyricId+2].timestamp)){
                    // ここなら場所が正しい
                    nowLyricId = nowLyricId+1;
                    scrollToCenter(nowLyricId);
                }
                // 現在の秒数が 読み込まれている歌詞のもう一つより小さければ正常。
                if(sec <= convertTimestampToSeconds(lyricsData[nowLyricId+1].timestamp  )){
                    //  && sec >= convertTimestampToSeconds(lyricsData[nowLyricId-1].timestamp)
                    console.log('歌詞の位置正常')
                }else{
                    console.log('！！歌詞検索開始！！')
                    for(let i=0; i <= lyricsData.length; i++){
                        // console.log(lyricsData[i].timestamp)
                        if(sec >= convertTimestampToSeconds(lyricsData[i].timestamp)){
                            if(sec < convertTimestampToSeconds(lyricsData[i+1].timestamp)){
                                nowLyricId = i;
                                scrollToCenter(nowLyricId);
                                console.log('！発見！');
                            }
                        }
                    }
                }
            }
        }
        
    }
    // function syncedLyrics(sec){
    //     if(lyricsData[nowLyricId]['timestamp'] != null){
    //         // 現在の秒数が 現在の読み込み歌詞の以上であれば歌詞を一つ移動させる。
    //         if (sec >= convertTimestampToSeconds(lyricsData[nowLyricId+1].timestamp)){
    //             nowLyricId = nowLyricId+1;
    //             scrollToCenter(nowLyricId);
    //             // 現在の秒数が 読み込まれている歌詞のもう一つより小さければ正常。
    //             if(sec <= convertTimestampToSeconds(lyricsData[nowLyricId+1].timestamp  )){
    //                 //  && sec >= convertTimestampToSeconds(lyricsData[nowLyricId-1].timestamp)
    //                 console.log('歌詞の位置正常')
    //             }else{
    //                 console.log('！！歌詞検索開始！！')
    //                 for(let i=0; i <= lyricsData.length; i++){
    //                     // console.log(lyricsData[i].timestamp)
    //                     if(sec >= convertTimestampToSeconds(lyricsData[i].timestamp)){
    //                         if(sec < convertTimestampToSeconds(lyricsData[i+1].timestamp)){
    //                             syncedLyrics(i);
    //                             nowLyricId = i;
    //                             console.log('！発見！');
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }
        
    // }


    // 




// LRCファイルを配列に変換する。
function parseLRC(data) {
    let lines;
    try {
        lines = data.split('\n');
    } catch (error) {

    }
    
    const result = [];

    let count = 0;
    for (const line of lines) {
      const match = line.match(/\[(\d+:\d+\.\d+)\](.+)/);
      if (match) {
        const timestamp = match[1];
        const text = match[2];
        result.push({ timestamp, text, count });
        count++;
      }
      else{
        // console.log(line);
        result.push({timestamp: null, text: line });
      }
    }

    return result;
  }

     // HTMLに書き込む。
     function addLrc(lyricsData){
        lyricsArea = document.querySelector(".lyrics");
        lyricsArea.innerHTML = '';
        if(lyricsData != null){
            for (const line of lyricsData) {
                // console.log(convertTimestampToSeconds(line.timestamp))
                const paragraph = document.createElement('p');
                paragraph.textContent = line.text;
                paragraph.id = line.count;
                lyricsArea.appendChild(paragraph);
            }
            // scrollToCenter('10');
        }else{
            const paragraph = document.createElement('a');
            paragraph.target = '_blank';
            paragraph.textContent = '歌詞が見つかりませんでした。';
            paragraph.href = `https://kashinavi.com/s/search.php?search=${title}+${artist[0]['name']}&start=1&sort=&r=all`;
            lyricsArea.appendChild(paragraph);
        }

      }
    