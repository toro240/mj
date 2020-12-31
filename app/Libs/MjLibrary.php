<?php

namespace App\Libs;

class MjLibrary
{
    const prefix = 'p_';
    const folder = 'img/mj_icon/';
    const extension = '.gif';
    const HAIPAI_NUM = 13;
    const USER = 'user';
    const USER_SIMO = 'simo';
    const USER_TOI = 'toi';
    const USER_KAMI = 'kami';
    private $hai = array();
    private $wan_hai = array();

    private $mj_logic;

    public function __construct()
    {
        $this->mj_logic = new MjLogic();
    }

    public function init() {
        $hai_all_type = config('const.HAI');
        for ($i = 0; $i < 4; $i++) {
            foreach ($hai_all_type as $hai_type) {
                $this->hai[] = $hai_type;
            }
        }
        shuffle($this->hai);

        $this->set_wanhai();
    }

    private function set_wanhai() {
        $wan_pai_count = 14;
        for ($i = 0; $i < $wan_pai_count; $i++) {
            $this->wan_hai[] = array_pop($this->hai);
        }
        $this->wan_hai = array_reverse($this->wan_hai);
    }

    public function get_haipai($position) {
        $haipai = self::get_hai_for_deck(self::HAIPAI_NUM, $position);
        self::hai_sort($haipai);
        return $haipai;
    }

    public function get_other_info() {
        // TODO 親決め
        $participant = array(
            self::USER,
            self::USER_SIMO,
            self::USER_TOI,
            self::USER_KAMI,
        );
        $parent = self::decide_parent($participant);

        $info = array(
            'parent' => $parent,
            'user_info' => array(),
            'dora' => $this->get_init_dora(),
            'next_tumo' => $parent,
        );

        return $info;
    }

    private function decide_parent($users) {
        $rand = rand(0, count($users) - 1);
        return $users[$rand];
    }

    private function get_init_dora() {
        $dora_list = array();

        $dora_count = 4;
        $dora_start_index = count($this->wan_hai) - 5;
        for($i = 0; $i < $dora_count; $i++) {
            $tmp_dora_list = array();
            // 最初のドラ
            if($i === 0) {
                $tmp_dora_list['name'] = $this->wan_hai[$dora_start_index];
                $tmp_dora_list['img'] = self::get_kawa_img($tmp_dora_list['name']);
                $tmp_dora_list['is_dora'] = true;
            } else {
                $tmp_dora_list['name'] = $this->wan_hai[$dora_start_index];
                $tmp_dora_list['img'] = 'img/mj_icon/p_bk_1.gif';
                $tmp_dora_list['is_dora'] = false;
            }

            $dora_list[] = $tmp_dora_list;

            $dora_start_index -= 2;
        }
        return $dora_list;
    }

    public function get_yama() {
        $yama = $this->hai;
        return $yama;
    }

    public function get_wan_hai() {
        $wan_hai = $this->wan_hai;
        return $wan_hai;
    }

    public function get_hai($data) {
        $this->hai = $data['yama'];
        $position = self::get_next_tumo_position($data['other_info']['next_tumo']);

        $hai = self::get_hai_for_deck(1, $position);

        switch ($position) {
            case config('const.POSITION.USER'):
                $data['user_haipai'][] = $hai;
                $data['other_info']['next_remove'] = self::USER;
                break;
            case config('const.POSITION.SIMO'):
                $data['simo_haipai'][] = $hai;
                $data['other_info']['next_remove'] = self::USER_SIMO;
                break;
            case config('const.POSITION.TOI'):
                $data['toi_haipai'][] = $hai;
                $data['other_info']['next_remove'] = self::USER_TOI;
                break;
            case config('const.POSITION.KAMI'):
                $data['kami_haipai'][] = $hai;
                $data['other_info']['next_remove'] = self::USER_KAMI;
                break;
            default:
                break;
        }

        $data['yama'] = $this->hai;

        return $data;
    }

    public function remove_hai($data) {
        $this->hai = $data['yama'];

        $position = self::get_next_tumo_position($data['other_info']['next_tumo']);

        switch ($position) {
            case config('const.POSITION.USER'):
                $remove_target = self::remove_target_hai($data['user_haipai'], $position);
                $data['kawa']['user_kawa'][] = $remove_target;
                $data['other_info']['next_tumo'] = self::USER_SIMO;
                $data['other_info']['user_info']['is_reach'] = $remove_target['is_reach'];
                break;
            case config('const.POSITION.SIMO'):
                $data['simo_haipai'][count($data['simo_haipai']) - 1]['remove'] = true;
                $remove_target = self::remove_target_hai($data['simo_haipai'], $position);
                $data['kawa']['simo_kawa'][] = $remove_target;
                $data['other_info']['next_tumo'] = self::USER_TOI;
                break;
            case config('const.POSITION.TOI'):
                $data['toi_haipai'][count($data['toi_haipai']) - 1]['remove'] = true;
                $remove_target = self::remove_target_hai($data['toi_haipai'], $position);
                $data['kawa']['toi_kawa'][] = $remove_target;
                $data['other_info']['next_tumo'] = self::USER_KAMI;
                break;
            case config('const.POSITION.KAMI'):
                $data['kami_haipai'][count($data['kami_haipai']) - 1]['remove'] = true;
                $remove_target = self::remove_target_hai($data['kami_haipai'], $position);
                $data['kawa']['kami_kawa'][] = $remove_target;
                $data['other_info']['next_tumo'] = self::USER;
                break;
            default:
                break;
        }

        return $data;
    }

    private function remove_target_hai(&$data, $position) {
        $remove_target = array();
        foreach ($data as $key => &$hai) {

            if(isset($hai['remove']) && $hai['remove'] === true) {
                $remove_target = array_splice($data, $key, 1)[0];
                break;
            }
        }

        self::hai_sort($data);
        $remove_target['img'] = self::get_kawa_img($remove_target['name'], $remove_target['is_reach']);

        return $remove_target;
    }

    private function get_next_tumo_position($next_tumo) {
        $position = 0;
        switch ($next_tumo) {
            case self::USER:
                $position = config('const.POSITION.USER');
                break;
            case self::USER_SIMO:
                $position = config('const.POSITION.SIMO');
                break;
            case self::USER_TOI:
                $position = config('const.POSITION.TOI');
                break;
            case self::USER_KAMI:
                $position = config('const.POSITION.KAMI');
                break;
        }

        return $position;
    }

    private function get_hai_for_deck($num, $position) {
        $hai_name_list = array_splice($this->hai, 0, $num);

        $result = array();
        foreach ($hai_name_list as $hai) {
            $tmp_result = array();
            $tmp_result['name'] = $hai;
            $tmp_result['img'] = self::get_haipai_img($hai, $position);
            $tmp_result['remove'] = false;
            $tmp_result['is_reach'] = false;

            array_push($result, $tmp_result);
        }

        if($num === 1) {
            return $result[0];
        }

        return $result;
    }

    public function hai_sort(&$hai_list)
    {
        $ms_hai_list = config('const.HAI');
        foreach ($hai_list as &$hai) {
            foreach ($ms_hai_list as $key => $value) {
                if($hai['name'] == $value) {
                    $hai['sort'] = $key;
                }
            }
        }

        $sort = array();
        foreach($hai_list as $key => $value){
            $sort[$key] = $value['sort'];
        }

        array_multisort($sort, SORT_ASC, $hai_list);
    }

    private function get_kawa_img($hai_name, $is_reach = false) {
        $haipai_img = '';
        if($is_reach === true) {
            $haipai_img = self::get_hai_img($hai_name).'_3';
        } else {
            $haipai_img = self::get_hai_img($hai_name).'_1';
        }
        return $haipai_img.self::extension;
    }

    private function get_haipai_img($hai_name, $position) {
        $haipai_img = '';
        switch ($position) {
            case config('const.POSITION.USER'):
            case config('const.POSITION.SIMO'):
            case config('const.POSITION.KAMI'):
            case config('const.POSITION.TOI'):
                $haipai_img = self::get_hai_img($hai_name).'_0';
                break;
            default:
                break;
        }
        return $haipai_img.self::extension;
    }

    private function get_hai_img($hai_name) {
        $img = self::folder.self::prefix;
        $hai_type = self::get_hai_type($hai_name);
        $number = self::get_hai_number($hai_name);
        switch ($hai_type) {
            case config('const.HAI_TYPE.MAN');
                $img .= 'ms'.$number;
                break;
            case config('const.HAI_TYPE.PIN');
                $img .= 'ps'.$number;
                break;
            case config('const.HAI_TYPE.SOU');
                $img .= 'ss'.$number;
                break;
            case config('const.HAI_TYPE.OTHER');
                $img .= 'ji_'.$number;
                break;
            default:
                break;
        }

        return $img;
    }

    private function get_hai_number($hai_name) {
        if(mb_strlen($hai_name) === 1) {
            return $hai_name;
        }

        return mb_substr($hai_name, -2, 1);
    }

    public function get_hai_type($hai_name) {
        $hai_type = 0;
        $length = mb_strlen($hai_name);
        if($length === 1) {
            $hai_type = config('const.HAI_TYPE.OTHER');
        } else {
            $last_str = mb_substr($hai_name, -1);
            switch ($last_str) {
                case 'm':
                    $hai_type = config('const.HAI_TYPE.MAN');
                    break;
                case 'p':
                    $hai_type = config('const.HAI_TYPE.PIN');
                    break;
                case 's':
                    $hai_type = config('const.HAI_TYPE.SOU');
                    break;
                default:
                    break;
            }

        }

        return $hai_type;
    }

    public function get_hai_all_type() {
        $hai_all_type = config('const.HAI');

        $result = array();
        foreach($hai_all_type as &$hai) {
            $tmp_result = array();
            $tmp_result['name'] = $hai;
            $tmp_result['img'] = self::get_haipai_img($hai, config('const.POSITION.USER'));

            $result[] = $tmp_result;
        }

        return $result;
    }
    
    public function hantei($data) {
        $tehai = $data['tehai'];
        $other = $data['other_info'];
        $mj_logic_index_list = self::convert_mj_logic($tehai);
        $tmp_other = array();
        $tmp_other[]['name'] = $other['ba_kaze'];
        $ba_kaze = self::convert_mj_logic($tmp_other)[0];
        $tmp_other = array();
        $tmp_other[]['name'] = $other['zi_kaze'];
        $zi_kaze = self::convert_mj_logic($tmp_other)[0];
        $agari_hai = array_pop($mj_logic_index_list);
        $this->mj_logic->set_tehai($mj_logic_index_list, $agari_hai, $other['is_tumo'], $ba_kaze, $zi_kaze);
        $this->mj_logic->calc();

        $result = array(
            'yaku' => $this->mj_logic->get_result_yaku(),
            'han' => $this->mj_logic->get_result_han(),
            'ten' => $this->mj_logic->get_result_ten(),
            'hu' => $this->mj_logic->get_result_hu(),
        );

        return $result;
    }

    private function convert_mj_logic($tehai) {

        $result = array();
        foreach($tehai as $hai) {
            $hai_type = self::get_hai_type($hai['name']);

            $hai_index = 0;
            switch ($hai_type) {
                case config('const.HAI_TYPE.MAN');
                    $hai_number = self::get_hai_number($hai['name']);
                    $hai_index = $hai_number;
                    break;
                case config('const.HAI_TYPE.PIN');
                    $hai_number = self::get_hai_number($hai['name']);
                    $hai_index = $hai_number + 10;
                    break;
                case config('const.HAI_TYPE.SOU');
                    $hai_number = self::get_hai_number($hai['name']);
                    $hai_index = $hai_number + 20;
                    break;
                case config('const.HAI_TYPE.OTHER');
                    $hai_number = self::other_index($hai['name']);
                    $hai_index = $hai_number + 30;
                    break;
                default:
                    break;
            }

            $result[] = $hai_index;
        }

        return $result;
    }

    private function other_index($other) {
        $index = 0;
        switch ($other) {
            case 'e':
                $index = 1;
                break;
            case 's':
                $index = 2;
                break;
            case 'w':
                $index = 3;
                break;
            case 'n':
                $index = 4;
                break;
            case 'h':
                $index = 5;
                break;
            case 'r':
                $index = 6;
                break;
            case 'c':
                $index = 7;
                break;
            default:
                break;
        }

        return $index;
    }


}
