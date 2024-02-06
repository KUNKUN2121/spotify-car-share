<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        /* Add your custom styles here */
        #lyricsArea {
          height: 300px; /* Set a height for the lyrics area */
          overflow-y: auto; /* Add a vertical scrollbar if lyrics overflow */
        }

        #progressBarContainer {
          margin-top: 20px;
        }
      </style>
</head>

<body>
    <a href="/spotify/login">ログイン</a>
    <a href="spotify/play">再生</a>
    @if( isset($title) )
        {{-- {{ $title ?? '再生されていません。' }}
        {{ $artist ?? '再生されていません。' }}
        <img src="{{$albumArt}}" alt=""> --}}

        <div class="container-fluid mt-5">
            <div class="row">
              <div class="col-md-6 text-center">
                <!-- Left Side: Album Art, Song Info, Controls -->
                <img src="{{ $albumArt ?? '' }}" alt="Album Art" class="img-fluid rounded mx-auto d-block">
                <h3 class="mt-3">{{ $title ?? '' }}</h3>
                <p>{{ $artist ?? '' }}</p>
                <div class="text-center mt-3">
                  <!-- Progress Bar and Time Display -->
                  <div id="progressBarContainer">
                    <div class="progress">
                      <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div id="timeDisplay" class="mt-2">0:00 / 0:00</div>
                  </div>
                  <!-- Controls -->
                  <button id="backBtn" class="btn btn-primary"><i class="fas fa-backward"></i></button>
                  <button id="pausePlayBtn" class="btn btn-primary ml-2"><i class="fas fa-pause"></i></button>
                  <button id="skipBtn" class="btn btn-primary ml-2"><i class="fas fa-forward"></i></button>
                </div>
              </div>
              <div class="col-md-6">
                <!-- Right Side: Lyrics Display Area -->
                <div class="text-center">
                  <h5>Lyrics</h5>
                  <p id="lyricsArea">Lyrics will appear here.</p>
                </div>
              </div>
            </div>
          </div>
    @endif

</body>
</html>

<script>
            progressBar();
        function progressBar(){
            const progress = document.querySelector("#progressBar");
            percent = ( {{ $progressMs }} / {{ $durationMs }} )  * 100
            progress.style.width = percent+"%";
            return ;
        }
</script>
