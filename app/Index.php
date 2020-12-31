<?php

namespace App;

use App\Libs\MjLibrary;
use Illuminate\Database\Eloquent\Model;

class Index extends Model
{
    private $mjLib;

    public function __construct()
    {
        $this->mjLib = new MjLibrary();
    }

    function init() {
        $this->mjLib->init();
    }

    function get_user_haipai() {
        return $this->mjLib->get_haipai(config('const.POSITION.USER'));
    }

    function get_simo_haipai() {
        return $this->mjLib->get_haipai(config('const.POSITION.SIMO'));
    }

    function get_toi_haipai() {
        return $this->mjLib->get_haipai(config('const.POSITION.TOI'));
    }

    function get_kami_haipai() {
        return $this->mjLib->get_haipai(config('const.POSITION.KAMI'));
    }

    function get_other_info() {
        return $this->mjLib->get_other_info();
    }

    function get_yama() {
        return $this->mjLib->get_yama();
    }

    function get_hai($data) {
        return $this->mjLib->get_hai($data);
    }

    function remove_hai($data) {
        return $this->mjLib->remove_hai($data);
    }
    
    function cpu_remove($data) {
        return $this->mjLib->cpu_remove($data);
    }
}
