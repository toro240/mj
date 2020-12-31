<?php

namespace App\Libs;

class MjLogic
{
    /**
     * @var array
     * 判定対象牌
     * key => index
     * value => 枚数
     */
    private $tehai;
    /**
     * @var int
     * 上がり牌
     * value index
     */
    private $agari_hai;
    /**
     * @var bool
     * ツモかどうか
     */
    private $is_tumo;
    /**
     * @var int
     * 場風
     * value index
     */
    private $ba_kaze;
    /**
     * @var int
     * 自風
     * value index
     */
    private $zi_kaze;

    private $tmp_tehai;

    private $head = 0;
    private $time = array();
    private $order = array();
    private $cnt_time = 0;
    private $cnt_order = 0;	// 刻子,順子のカウント

    private $result_yaku = array();
    private $result_han = 0;
    private $result_ten = 0;
    private $result_hu = 0;

    private $tmp_result_yaku = array();
    private $tmp_result_han = 0;
    private $tmp_result_ten = 0;
    private $tmp_result_hu = 0;

    private $yakuman_ten = 32000;
    private $w_yakuman_ten = 64000;
    private $t_yakuman_ten = 96000;

    private $ms_yaku_name_list;
    private $ms_yaku_han_list;

    public function __construct()
    {
        $this->ms_yaku_name_list = config('const.YAKU_NAME');
        $this->ms_yaku_han_list = config('const.YAKU_HAN');
    }
    /**
     * フィールドに手牌情報を格納する
     *
     * @param array  $tehai 手牌
     * @param [type]  $agari_hai [description]
     * @param boolean $is_tumo   [description]
     * @param [type]  $ba_kaze   [description]
     * @param [type]  $zi_kaze   [description]
     */
    public function set_tehai($tehai, $agari_hai, $is_tumo, $ba_kaze, $zi_kaze) {
        $this->tehai = array();
        foreach ($tehai as $hai_index) {
            if(isset($this->tehai[$hai_index]) === true) {
                $this->tehai[$hai_index] += 1;
            } else {
                $this->tehai[$hai_index] = 1;
            }
        }

        if(isset($this->tehai[$agari_hai]) === true) {
            $this->tehai[$agari_hai] += 1;
        } else {
            $this->tehai[$agari_hai] = 1;
        }

        ksort($this->tehai);

        $this->agari_hai = $agari_hai;
        $this->is_tumo = $is_tumo;
        $this->ba_kaze = $ba_kaze;
        $this->zi_kaze = $zi_kaze;
    }

    public function calc() {
        self::reset_tmp();
        if(self::check_kokushi() === true) {
            self::set_result_kokushi();
            return true;
        }

        self::reset_tmp();
        foreach ($this->tmp_tehai as $index => $num) {
            self::reset_tmp();
            if($num >= 2) {
                // 雀頭
                $this->tmp_tehai[$index] -= 2;
                $this->head = $index;
                // 刻子→順子
                self::check_time();
                self::check_order();
                if(self::check_use_out() === true) {
                    self::set_score_to_high_score();
                }
                // 順子→刻子
                self::reset_tmp();
                $this->tmp_tehai[$index] -= 2;
                $this->head = $index;
                self::check_order();
                self::check_time();
                if(self::check_use_out() === true) {
                    self::set_score_to_high_score();
                }
                // 刻子取り出し→順子→刻子
                self::reset_tmp();
                $this->tmp_tehai[$index] -= 2;
                $this->head = $index;
                self::check_time(1);
                self::check_order();
                self::check_time();
                if(self::check_use_out() === true) {
                    self::set_score_to_high_score();
                }
            }
        }

        return true;
    }

    public function get_result_yaku() {
        if(empty($this->result_yaku)) {
            $this->result_yaku[] = 'チョンボ!!!!!!!!!!!!!!!!!!!!!';
        }


        return $this->result_yaku;
    }

    public function get_result_han() {
        return $this->result_han;
    }

    public function get_result_ten() {
        $this->result_ten = 0;

        if($this->result_han >= 13 && $this->result_han < 26) {
            $this->result_ten = $this->yakuman_ten;
        } else if($this->result_han >= 26 && $this->result_han < 39) {
            $this->result_ten = $this->w_yakuman_ten;
        } else if($this->result_han >= 39) {
            $this->result_ten = $this->t_yakuman_ten;
        } else if($this->result_han === 0){
            $this->result_ten = -8000;
        } else {

        }
        return $this->result_ten;
    }

    public function get_result_hu() {

        if($this->result_han >= 13) {
            $this->result_hu = 0;
        } else {

        }

        return $this->result_hu;
    }

    private function reset_tmp() {
        $this->time = array();
        $this->order = array();
        $this->cnt_time = 0;
        $this->cnt_order = 0;
        $this->head = 0;
        $this->tmp_tehai = $this->tehai;
    }

    private function check_use_out() {
        foreach ($this->tmp_tehai as $hai) {
            if($hai > 0) {
                return false;
            }
        }
        return true;
    }

    private function check_time($num_key = 0) {
        foreach($this->tmp_tehai as $index => $num) {
            if($num >= 3) {
                $this->tmp_tehai[$index] -= 3;
                $this->time[$this->cnt_time++] = $index;
                if($num > 0 && $this->cnt_time === $num_key) {
                    return;
                }
            }
        }
    }

    private function check_order() {
        foreach($this->tmp_tehai as $index => $num) {
            // 8以上は行わない || 字牌は行わない
            if ($index % 10 > 7 || $index > 30) {
                continue;
            }
            while(isset($this->tmp_tehai[$index])
                && isset($this->tmp_tehai[$index + 1])
                && isset($this->tmp_tehai[$index + 2])
                && $this->tmp_tehai[$index] > 0
                && $this->tmp_tehai[$index + 1] > 0
                && $this->tmp_tehai[$index + 2] > 0){
                $this->tmp_tehai[$index]--;
                $this->tmp_tehai[$index + 1]--;
                $this->tmp_tehai[$index + 2]--;
                $this->order[$this->cnt_order++] = $index;
            }
        }
    }

    private function get_order_hai($index) {
        $order_hai = array($index, $index + 1, $index + 2);
        return $order_hai;
    }
    private function set_score_to_high_score() {
        // 役満
        self::set_suanko();
        self::set_daisangen();
        self::set_ryuiso();
        self::set_tuiso();
        self::set_tinroto();
        self::set_sho_dai_sushi();
        self::set_tyuren();

        if($this->tmp_result_han === 0) {
            self::set_tinitu();
            self::set_honitu();
            self::set_honroto();
            self::set_shousangen();
            self::set_zyuntyan();
            self::set_ryanpeko();
            self::set_tyanta();
            self::set_toitoi();
            self::set_ittu();
            self::set_sananko();
            self::set_sansyoku_doko();
            self::set_sansyoku_douzyun();
            self::set_ipeko();
            self::set_tanyao();
            self::set_sangen();
            self::set_kaze();
            self::set_pinhu();
            self::set_tumo();
        }

        if($this->result_han < $this->tmp_result_han) {
            $this->result_yaku[] = $this->tmp_result_yaku;
            $this->result_han = $this->tmp_result_han;
            $this->result_ten = $this->tmp_result_ten;
            $this->result_hu = $this->tmp_result_hu;
        }

        $this->tmp_result_yaku = array();
        $this->tmp_result_han = 0;
        $this->tmp_result_ten = 0;
        $this->tmp_result_hu = 0;
    }

    private function set_suanko() {
        //TODO ポンしているかの判定
        if($this->cnt_time === 4 && $this->head === $this->agari_hai) {
            $this->set_yaku_han('SUTTAN');
        } else if($this->cnt_time === 4 && $this->is_tumo) {
            $this->set_yaku_han('SUANKO');
        }
    }

    private function get_sangen_pai() {
        return array(35,36,37);
    }

    private function get_kaze_hai() {
        return array(31,32,33,34);
    }

    private function get_one_nine_hai() {
        return array(1,9,11,19,21,29);
    }

    private function set_daisangen() {
        if($this->cnt_time < 3) {
            return;
        }
        // 3になれば大三元
        $cnt = 0;
        foreach($this->time as $time) {
            if(in_array($time, self::get_sangen_pai())) {
                $cnt++;
            }
        }
        if($cnt === 3) {
            $this->set_yaku_han('DAISANGEN');
        }
    }

    private function set_ryuiso() {
        $ryuiso_hai_list = array(22,23,24,26,28,36);

        if(!in_array($this->head, $ryuiso_hai_list)) {
            return;
        }

        foreach ($this->time as $time) {
            if(!in_array($time, $ryuiso_hai_list)) {
                return;
            }
        }

        foreach ($this->order as $order_index) {
            foreach (self::get_order_hai($order_index) as $order) {
                if(!in_array($order, $ryuiso_hai_list)) {
                    return;
                }
            }
        }

        $this->set_yaku_han('RYUISO');
    }

    private function set_tuiso() {
        $tuiso_border = 31;
        if($this->head < $tuiso_border) {
            return;
        }

        if($this->cnt_order > 0) {
            return;
        }

        foreach ($this->time as $time) {
            if($time < $tuiso_border) {
                return;
            }
        }

        $this->set_yaku_han('TUISO');
    }

    private function set_tinroto() {
        $tinroto_hai_list = self::get_one_nine_hai();

        if(!in_array($this->head, $tinroto_hai_list)) {
            return;
        }

        if($this->cnt_order > 0) {
            return;
        }

        foreach ($this->time as $time) {
            if(!in_array($time, $tinroto_hai_list)) {
                return;
            }
        }

        $this->set_yaku_han('TINROTO');
    }

    private function set_sho_dai_sushi() {
        $shosushi_hai_list = self::get_kaze_hai();
        $cnt = 0;
        foreach ($this->time as $time) {
            if(in_array($time, $shosushi_hai_list)) {
                $cnt++;
            }
        }
        if($cnt === 4) {
            $this->set_yaku_han('DAISUSHI');
            return;
        }

        if(in_array($this->head, $shosushi_hai_list)) {
            $cnt++;
        }

        if($cnt === 4) {
            $this->set_yaku_han('SHOSUSHI');
            return;
        }
    }

    private function set_tyuren() {
        if(!self::is_tinitu()) {
            return;
        }

        $hai_type = self::get_hai_type($this->head);

        $hai_start_index = 10 * ($hai_type - 1);
        if( !isset($this->tehai[$hai_start_index + 1])
            || !isset($this->tehai[$hai_start_index + 2])
            || !isset($this->tehai[$hai_start_index + 3])
            || !isset($this->tehai[$hai_start_index + 4])
            || !isset($this->tehai[$hai_start_index + 5])
            || !isset($this->tehai[$hai_start_index + 6])
            || !isset($this->tehai[$hai_start_index + 7])
            || !isset($this->tehai[$hai_start_index + 8])
            || !isset($this->tehai[$hai_start_index + 9])
        ) {
            return;
        }

        if($this->tehai[$hai_start_index + 1] < 3 && $this->tehai[$hai_start_index + 9] < 3) {
            return;
        }

        $tmp_tehai = $this->tehai;
        $tmp_tehai[$this->agari_hai]--;

        if( $tmp_tehai[$hai_start_index + 1] === 3
            && $tmp_tehai[$hai_start_index + 2] === 1
            && $tmp_tehai[$hai_start_index + 3] === 1
            && $tmp_tehai[$hai_start_index + 4] === 1
            && $tmp_tehai[$hai_start_index + 5] === 1
            && $tmp_tehai[$hai_start_index + 6] === 1
            && $tmp_tehai[$hai_start_index + 7] === 1
            && $tmp_tehai[$hai_start_index + 8] === 1
            && $tmp_tehai[$hai_start_index + 9] === 3
        ) {
            $this->set_yaku_han('ZYUNSEI_TYUREN');
        } else {
            $this->set_yaku_han('TYUREN');
        }
    }

    private function is_tinitu() {
        $hai_type = self::get_hai_type($this->head);
        if($hai_type === config('const.HAI_TYPE.OTHER')) {
            return false;
        }
        // チンイツチェック
        foreach ($this->time as $time) {
            if($hai_type !== $this->get_hai_type($time)) {
                return false;
            }
        }
        foreach ($this->order as $order_index) {
            foreach (self::get_order_hai($order_index) as $order) {
                if($hai_type !== $this->get_hai_type($order)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function set_tinitu() {
        if(!self::is_tinitu()) {
            return;
        }

        $this->set_yaku_han('TINITU');
    }

    private function set_honitu() {
        $hai_type = 0;
        $in_other = false;

        $head_hai_type = $this->get_hai_type($this->head);
        if($head_hai_type === config('const.HAI_TYPE.OTHER')) {
            $in_other = true;
        } else {
            $hai_type = $head_hai_type;
        }

        foreach ($this->time as $time) {
            $tmp_hai_type = $this->get_hai_type($time);

            if($tmp_hai_type === config('const.HAI_TYPE.OTHER')) {
                $in_other = true;
                continue;
            } else {
                if($hai_type === 0) {
                    $hai_type = $tmp_hai_type;
                } else if($hai_type !== 0 && $hai_type !== $tmp_hai_type){
                    return;
                }
            }
        }

        foreach ($this->order as $order_index) {
            foreach (self::get_order_hai($order_index) as $order) {
                $tmp_hai_type = $this->get_hai_type($order);

                if($hai_type === 0) {
                    $hai_type = $tmp_hai_type;
                } else if($hai_type !== 0 && $hai_type !== $tmp_hai_type) {
                    return;
                }
            }
        }

        if($in_other === true) {
            $this->set_yaku_han('HONITU');
        }
    }

    private function set_honroto() {
        if($this->cnt_order > 0) {
            return;
        }

        $honro_hai_list = array_merge(self::get_sangen_pai(), self::get_kaze_hai(), self::get_one_nine_hai());

        if(!in_array($this->head, $honro_hai_list)) {
            return;
        }

        foreach ($this->time as $time) {
            if(!in_array($time, $honro_hai_list)) {
                return;
            }
        }

        $this->set_yaku_han('HONROTO');
    }

    private function set_zyuntyan() {
        $zyuntyan_hai_list = self::get_one_nine_hai();

        if(!in_array($this->head, $zyuntyan_hai_list)) {
            return;
        }

        foreach ($this->time as $time) {
            if(!in_array($time, $zyuntyan_hai_list)) {
                return;
            }
        }

        foreach ($this->order as $order_index) {
            $is_in_one_nine = false;
            foreach (self::get_order_hai($order_index) as $order) {
                if(in_array($order, $zyuntyan_hai_list)) {
                    $is_in_one_nine = true;
                }
            }

            if($is_in_one_nine === false) {
                return;
            }
        }

        $this->set_yaku_han('ZYUNTYAN');
    }

    private function set_shousangen() {
        $sangen_pai_list = self::get_sangen_pai();
        if(!in_array($this->head, $sangen_pai_list)) {
            return;
        }

        $cnt = 0;
        foreach ($this->time as $time) {
            if(in_array($time, $sangen_pai_list)) {
                $cnt++;
            }
        }

        if($cnt === 2) {
            $this->set_yaku_han('SYOUSANGEN');
        }
    }

    private function set_ryanpeko() {
        if($this->cnt_time > 0) {
            return;
        }

        if($this->cnt_order !== 4) {
            return;
        }

        foreach ($this->tehai as $num) {
            if(!($num === 2 || $num === 4 )) {
                return;
            }
        }

        $this->set_yaku_han('RYANPEKO');
    }

    private function set_tyanta() {
        if($this->cnt_time === 0 || $this->cnt_order === 0) {
            return;
        }

        $ji_hai_list = array_merge(self::get_sangen_pai(), self::get_kaze_hai());
        $tyanta_hai_list = array_merge($ji_hai_list, self::get_one_nine_hai());

        if(!in_array($this->head, $tyanta_hai_list)) {
            return;
        }

        foreach ($this->time as $time) {
            if(!in_array($time, $tyanta_hai_list)) {
                return;
            }
        }

        foreach ($this->order as $order_index) {
            $is_in_one_nine = false;
            foreach (self::get_order_hai($order_index) as $order) {
                if(in_array($order, $tyanta_hai_list)) {
                    $is_in_one_nine = true;
                }
            }

            if($is_in_one_nine === false) {
                return;
            }
        }

        $is_other = false;
        foreach ($this->tehai as $index => $num) {
            if(in_array($index, $ji_hai_list)) {
                $is_other = true;
                break;
            }
        }

        if($is_other === true) {
            $this->set_yaku_han('TYANTA');
        }
    }

    private function set_toitoi() {
        if($this->cnt_time === 4) {
            $this->set_yaku_han('TOITOI');
        }
    }

    private function set_ittu() {
        if($this->cnt_order < 3) {
            return;
        }

        if(
            (in_array(1, $this->order) && in_array(4, $this->order) && in_array(7, $this->order))
            || (in_array(11, $this->order) && in_array(14, $this->order) && in_array(17, $this->order))
            || (in_array(21, $this->order) && in_array(24, $this->order) && in_array(27, $this->order))
        ) {
            $this->set_yaku_han('ITTU');
        }
    }

    private function set_sananko() {
        if($this->cnt_time >= 3) {
            $this->set_yaku_han('SANANKO');
        }
    }

    private function set_sansyoku_doko() {
        if($this->cnt_time < 3) {
            return;
        }

        $man_time_list = array();
        $other_time_list = array();
        foreach ($this->time as $time) {
            $type = self::get_hai_type($time);
            // サンショクドーコーの場合はマンズあり
            if($type === config('const.HAI_TYPE.MAN')) {
                $man_time_list[] = $time;
            } else {
                $other_time_list[$time] = $time;
            }
        }

        foreach ($man_time_list as $man_time) {
            if(isset($other_time_list[$man_time + 10]) && isset($other_time_list[$man_time + 20])) {
                $this->set_yaku_han('SANSYOKU_DOUKOU');
                return;
            }
        }
    }

    private function set_sansyoku_douzyun() {
        if($this->cnt_order < 3) {
            return;
        }

        $man_order_list = array();
        $other_time_list = array();
        foreach ($this->order as $order_index) {
            $type = self::get_hai_type($order_index);
            // サンショクドージュンの場合はマンズあり
            if($type === config('const.HAI_TYPE.MAN')) {
                $man_order_list[] = $order_index;
            } else {
                $other_time_list[$order_index] = $order_index;
            }
        }

        foreach ($man_order_list as $man_order_index) {
            if(isset($other_time_list[$man_order_index + 10]) && isset($other_time_list[$man_order_index + 20])) {
                $this->set_yaku_han('SANSYOKU_DOUZYUN');
                return;
            }
        }
    }

    private function set_ipeko() {
        if($this->cnt_order < 2) {
            return;
        }

        if(array_unique($this->order) !== $this->order) {
            $this->set_yaku_han('IPEKO');
        }
    }

    private function set_tanyao() {
        $except_tanyao_hai_list = array_merge(self::get_sangen_pai(), self::get_kaze_hai(), self::get_one_nine_hai());

        if(in_array($this->head, $except_tanyao_hai_list)) {
            return;
        }

        foreach ($this->time as $time) {
            if(in_array($time, $except_tanyao_hai_list)) {
                return;
            }
        }

        foreach ($this->order as $order_index) {
            foreach (self::get_order_hai($order_index) as $order) {
                if(in_array($order, $except_tanyao_hai_list)) {
                    return;
                }
            }
        }

        $this->set_yaku_han('TANYAO');
    }

    private function set_sangen() {
        foreach ($this->time as $time) {
            if($time === 35) {
                $this->set_yaku_han('HAKU');
                continue;
            }

            if($time === 36) {
                $this->set_yaku_han('HATU');
                continue;
            }

            if($time === 37) {
                $this->set_yaku_han('TYUN');
                continue;
            }
        }
    }

    private function set_kaze() {
        if($this->cnt_time === 0) {
            return;
        }

        foreach ($this->time as $time) {
            if($time === $this->ba_kaze) {
                $this->set_yaku_han('BAKAZE');
            }

            if($time === $this->zi_kaze) {
                $this->set_yaku_han('ZIKAZE');
            }
        }
    }

    private function set_pinhu() {
        if($this->cnt_time > 0) {
            return;
        }

        $sangen_pai_list = self::get_sangen_pai();

        if($this->head === $this->ba_kaze || $this->head === $this->zi_kaze || in_array($this->head, $sangen_pai_list)) {
            return;
        }

        foreach ($this->order as $order_index) {
            foreach (self::get_order_hai($order_index) as $key => $order) {
                if($key === 1) {
                    continue;
                }

                $key_one_digit = (int)substr($order, strlen($order) - 1, 2);
                $agari_hai_one_digit = (int)substr($this->agari_hai, strlen($this->agari_hai) - 1);
                if($key === 0 && $key_one_digit === 1 && $agari_hai_one_digit === 3) {
                    break;
                }

                if($key === 0 && $key_one_digit === 7 && $agari_hai_one_digit === 7) {
                    break;
                }

                if($this->agari_hai == $order) {
                    $this->set_yaku_han('PINHU');
                    return;
                }
            }
        }
    }

    private function set_tumo() {
        if($this->is_tumo) {
            $this->set_yaku_han('TUMO');
        }
    }

    private function get_hai_type($hai) {
        $hai_type_list = config('const.HAI_TYPE');
        $hai_type = 0;
        switch ($hai) {
            case 1:case 2:case 3:case 4:case 5:case 6:case 7:case 8:case 9:
                $hai_type = $hai_type_list['MAN'];
                break;
            case 11:case 12:case 13:case 14:case 15:case 16:case 17:case 18:case 19:
                $hai_type = $hai_type_list['PIN'];
                break;
            case 21:case 22:case 23:case 24:case 25:case 26:case 27:case 28:case 29:
                $hai_type = $hai_type_list['SOU'];
                break;
            case 31:case 32:case 33:case 34:case 35:case 36:case 37:
                $hai_type = $hai_type_list['OTHER'];
                break;

        }

        return $hai_type;
    }

    private function set_yaku_han($key) {
        $this->tmp_result_yaku[] = $this->ms_yaku_name_list[$key];
        $this->tmp_result_han += $this->ms_yaku_han_list[$key];
    }

    private function check_kokushi() {
        // 頭があったらtrueになる
        $is_head = false;

        foreach ($this->tmp_tehai as $index => $hai_num) {
            switch($index) {
                case 1: case 9: case 11: case 19: case 21: case 29:
                case 31: case 32: case 33: case 34: case 35: case 36: case 37:
                if ($is_head === false && $this->tmp_tehai[$index] === 2) {
                    $this->tmp_tehai[$index] -= 2;
                    $is_head = true;
                } else if ($this->tmp_tehai[$index] === 1) {
                    $this->tmp_tehai[$index]--;
                }
                break;
                default:
                    break;
            }
        }

        return self::check_use_out();
    }

    private function set_result_kokushi() {
        if($this->tehai[$this->agari_hai] === 2) {
            $this->result_yaku[] = $this->ms_yaku_name_list['KOKUSHI_13'];
            $this->result_han += $this->ms_yaku_han_list['KOKUSHI_13'];
        } else {
            $this->result_yaku[] = $this->ms_yaku_name_list['KOKUSHI'];
            $this->result_han += $this->ms_yaku_han_list['KOKUSHI'];
        }
    }

}
