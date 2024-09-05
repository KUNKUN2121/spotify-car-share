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
            <a href="https://spy.agameru.work/app?roomId="></a>
        </div>
    @endif
    <a href="admin/create">ルーム作成</a>

</body>
</html>
