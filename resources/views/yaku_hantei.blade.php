<html>
<head>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>

    <link href="css/yaku_hantei.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="js/yaku_hantei.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
    <body>
        <div id="main">
        @foreach ($data as $hai)
            @if(($loop->index + 1) % 9 === 0)
                <img src={{$hai['img']}} name={{$hai['name']}} /><br />
            @else
                <img src={{$hai['img']}} name={{$hai['name']}} />
            @endif
        @endforeach
        </div>

        <div id="tehai">
        </div>

        <div id="pon">
        </div>

        <div>
            <p><input name="is_tumo" type="checkbox" />ツモ</p>
            <p>場風 :
                <input type="radio" name="bakaze" value="e" checked>東
                <input type="radio" name="bakaze" value="s">南
            </p>
            <p>自風 :
                <input type="radio" name="zikaze" value="e" checked>東
                <input type="radio" name="zikaze" value="s">南
                <input type="radio" name="zikaze" value="w">西
                <input type="radio" name="zikaze" value="n">北
            </p>
            <p><input id="is_pon" type="checkbox" />ポン</p>
            <button id="yaku_hantei">
                役判定
            </button>
        </div>

        <div id="result">
            <p>役:<span id="yaku"></span></p>
            <p>点数:<span id="ten"></span></p>
            <p>翻数:<span id="han"></span></p>
            <p>符:<span id="hu"></span></p>
        </div>
    </body>
</html>