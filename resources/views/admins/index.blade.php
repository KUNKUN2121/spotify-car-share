<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    {{\Illuminate\Support\Facades\Auth::user()->id}}でログインしています。
    {{-- {{
        if($roomInfo) ? $roomInfo->room_id : "";

    }} --}}

    @if($roomInfo)
        <div>
            <p>roomIDは  {{$roomInfo->room_id}}</p>
           <p> 参加リンク </p>
            <a id ="joinLink" href="">参加用リンク</a>
            <br />
            <button onclick="copyLink()">コピーする</button>
        </div>
        <div>
           <a href="/admin/room/delete">ルームを削除</a>
        </div>
    @else
        <p>ルームが作成されていません</p>
        <a href="/admin/room/create">ルーム作成</a>
        <a href="/admin/account/delete">アカウント削除</a>
    @endif

    <script>
            function copyLink() {
                // 1. リンクのhref属性を取得
                const link = document.getElementById('joinLink').href;

                // 2. クリップボードにコピー
                navigator.clipboard.writeText(link)
                .then(() => {
                    alert('リンクがコピーされました: ' + link);
                })
                .catch(err => {
                    alert('コピーに失敗しました: ' + err);
                });
            }

            function getLink() {
                var currentUrl = window.location.origin;
                return currentUrl + "/?roomId=" + "{{ $roomInfo ? $roomInfo->room_id : '' }}";
            }

            document.getElementById('joinLink').href = getLink();
    </script>

</body>
</html>
