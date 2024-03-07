document.getElementsByClassName("progress-bar")[0].style.backgroundSize = '0%'
document.getElementsByClassName("progress-bar")[0].addEventListener("click", (e) => {
    const mouse = e.pageX;
    console.log(mouse)
    console.log(e);
   document.getElementsByClassName("progress-bar")[0].style.backgroundSize = '50%'
});



  // LRCのタイムをSecに変換する
  function convertTimestampToSeconds(timestamp) {
    const [minutes, seconds] = timestamp.split(':').map(parseFloat);
    return minutes * 60 + seconds;
    }

 // Ms を Secに変換する
 function convertMsToSec(value){
    return seconds = Math.floor(value / 1000);
 }



document.querySelector('.progress').innerHTML = convertMsToSec(progressMs)
document.querySelector('.duration').innerHTML

