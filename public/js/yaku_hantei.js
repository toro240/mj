$(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function add_tehai($dom) {
        if($('#tehai img').length + $('#pon img').length === 14) {
            return false;
        }

        append_src = $dom.attr('src');
        counter = 0;
        $("#tehai img").each(function(i, img){
            var src = $(img).attr('src');
            if(src == append_src) {
                counter++;
            }
        });

        if(counter > 3) {
            return false;
        }

        $('#tehai').append($dom);
    }
    
    function add_pon($dom) {
        if($('#pon img').length === 12) {
            add_tehai($dom);
            return false;
        }

        append_src = $dom.attr('src');
        counter = 0;
        $("#pon img").each(function(i, img){
            var src = $(img).attr('src');
            if(src == append_src) {
                counter++;
            }
        });

        $pon_element_dom = $('<div>', {class:'pon_element', style:"width:130px;display:inline-block;"});

        $clone = $dom.clone();
        $clone.attr('style', 'transform: rotate(-90deg);margin-right: 13px; margin-left: 13px;');
        $pon_element_dom.append($clone);
        $pon_element_dom.append($dom.clone());
        $pon_element_dom.append($dom.clone());
        $('#pon').append($pon_element_dom);
    }

    function yaku_hantei(tehai) {
        $.ajax({
            type: "POST",
            url: "yaku_hantei/hantei",
            dataType: 'json',
            data: {
                "data": JSON.stringify(tehai)
            },
            success: function (json) {
                json = $.parseJSON(json);
                result = json['result'];

                $('#result #ten').text(result['ten']);

                var yaku_html = '';
                for(var i = 0; i < result['yaku'].length; i++) {
                    yaku_html += result['yaku'][i] + '<br />';
                }
                $('#result #yaku').html(yaku_html);
                $('#result #han').text(result['han']);
                $('#result #hu').text(result['hu']);
            }
        });
    }

    function get_input_value() {
        $result = {};

        $result['is_tumo'] = $("[name=is_tumo]").prop("checked");
        $result['ba_kaze'] = $('input[name=bakaze]:checked').val();
        $result['zi_kaze'] = $('input[name=zikaze]:checked').val();

        return $result;
    }


    $(document).on("click", "#main img", function () {
        if($("#is_pon").prop("checked")) {
            add_pon($(this).clone(true));
        } else {
            add_tehai($(this).clone(true));
        }
    });

    $(document).on("click", "#tehai img", function () {
        $(this).remove();
    });

    $(document).on("click", "#yaku_hantei", function () {
        if($('#tehai img').length + $('#pon img').length <= 13) {
            return false;
        }

        tehai = {};
        tehai['tehai'] = {};
        tehai['other_info'] = {};
        counter = 0;
        $("#tehai img").each(function(i, img){
            var tmp_tehai = {};
            tmp_tehai['name'] = $(img).attr('name');
            tmp_tehai['img'] = $(img).attr('src');

            tehai['tehai'][counter++] = tmp_tehai;
        });

        $("#pon img").each(function(i, img){
            var tmp_tehai = {};
            tmp_tehai['name'] = $(img).attr('name');
            tmp_tehai['img'] = $(img).attr('src');
            tmp_tehai['is_pon'] = true;

            tehai['tehai'][counter++] = tmp_tehai;
        });

        tehai['other_info'] = get_input_value();
        yaku_hantei(tehai);
    });
});