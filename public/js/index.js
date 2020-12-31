$(function () {
    var json_data = [];
    var reach_flg = false;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
// 初期化処理
    function init() {
        $.ajax({
            type: "GET",
            url: "get_haipai",
            dataType: 'json',
            contentType: 'json',
            headers: {
                'Content-Type': 'application/json'
            },
            success: function (json) {
                json = $.parseJSON(json);
                json_data = json;
                makeView(json);
                makeDora(json['other_info']['dora']);
                getHai(json);
            }
        });
    }

    function makeView(jsondata) {
        makeHaipai($('#my-hand'), jsondata['user_haipai']);
        makeHaipai($('#simo-hand'), jsondata['simo_haipai']);
        makeHaipai($('#toi-hand'), jsondata['toi_haipai']);
        makeHaipai($('#kami-hand'), jsondata['kami_haipai']);
    }

    function makeDora(dora_list) {
        $.each(dora_list,function(key, value){
            var img = value['img'];
            tag = '<img src=' +  img + ' />';
            $('#dora-hai').append(tag);
        });
    }

    function makeHaipai($dom, haipai) {
        $dom.empty();
        $.each(haipai,function(key, value){
            var img = value['img'];
            tag = '<img src=' +  img + ' />';
            $dom.append(tag);
        });
    }

    function makeKawa(nextremove, kawa) {
        var $dom = null;
        var target_kawa = [];
        switch (nextremove) {
            case 'user':
                $dom = $('#my-kawa');
                target_kawa = kawa['user_kawa'];
                break;
            case 'simo':
                $dom = $('#simo-kawa');
                target_kawa = kawa['simo_kawa'];
                break;
            case 'toi':
                $dom = $('#toi-kawa');
                target_kawa = kawa['toi_kawa'];
                break;
            case 'kami':
                $dom = $('#kami-kawa');
                target_kawa = kawa['kami_kawa'];
                break;
        }

        var target_hai = target_kawa[target_kawa.length - 1];
        var img = target_hai['img'];
        var tag = '<img src=' +  img + ' />';
        $dom.append(tag);
    }

    function getHai(json) {

        $.ajax({
            type: "POST",
            url: "get_hai",
            dataType: 'json',
            data: {
                "data": JSON.stringify(json)
            },
            success: function (json) {
                json = $.parseJSON(json);
                json_data = json;
                addHai(json);
                
                if(json['other_info']['next_remove'] !== 'user') {
                    removeHai(json);
                }
            }
        });
    }

    function addHai(json) {
        switch (json['other_info']['next_tumo']) {
            case 'user':
                hai = json['user_haipai'][json['user_haipai'].length - 1];
                addHaiView($('#my-hand'), hai);
                break;
            case 'simo':
                hai = json['simo_haipai'][json['simo_haipai'].length - 1];
                addHaiView($('#simo-hand'), hai);
                break;
            case 'toi':
                hai = json['toi_haipai'][json['toi_haipai'].length - 1];
                addHaiView($('#toi-hand'), hai);
                break;
            case 'kami':
                hai = json['kami_haipai'][json['kami_haipai'].length - 1];
                addHaiView($('#kami-hand'), hai);
                break;
            default:
                break;
        }
    }

    function addHaiView($dom, hai) {
        var img = hai['img'];
        tag = '<img src=' + img + ' />';
        $dom.append(tag);
    }

    function removeHai(json) {
        $.ajax({
            type: "POST",
            url: "remove_hai",
            dataType: 'json',
            data: {
                "data": JSON.stringify(json)
            },
            success: function (json) {
                json = $.parseJSON(json);
                json_data = json;
                switch (json['other_info']['next_remove']) {
                    case 'user':
                        makeHaipai($('#my-hand'), json['user_haipai']);
                        break;
                    case 'simo':
                        makeHaipai($('#simo-hand'), json['simo_haipai']);
                        break;
                    case 'toi':
                        makeHaipai($('#toi-hand'), json['toi_haipai']);
                        break;
                    case 'kami':
                        makeHaipai($('#kami-hand'), json['kami_haipai']);
                        break;

                }
                makeKawa(json['other_info']['next_remove'], json['kawa']);
                getHai(json);
            }
        });
    }

    $(document).on("click", "#my-hand img", function () {
        if($('#my-hand img').length !== 14) {
            return false;
        }
        var index = $('#my-hand img').index(this);
        json_data['user_haipai'][index]['remove'] = true;
        json_data['user_haipai'][index]['is_reach'] = reach_flg;
        removeHai(json_data);
    });

    $(document).on("click", "#reach", function () {
        if($('#my-hand img').length !== 14) {
            return false;
        }
        reach_flg = true;
        $(this).prop("disabled", true);
    });

    init();
});