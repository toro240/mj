<?php

namespace App;

use App\Libs\MjLibrary;
use Illuminate\Database\Eloquent\Model;

class YakuHantei extends Model
{

    private $mjLib;
    public function __construct()
    {
        $this->mjLib = new MjLibrary();
    }

    public function show() {
        return $this->mjLib->get_hai_all_type();
    }

    public function hantei($data) {
        return $this->mjLib->hantei($data);
    }
}
