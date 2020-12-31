<?php

namespace App\Http\Controllers;

use App\YakuHantei;
use Illuminate\Http\Request;

class YakuHanteiController extends Controller
{
    private $yaku_hantei;

    function __construct()
    {
        $this->yaku_hantei = new YakuHantei();
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $data = $this->yaku_hantei->show();
        return view('yaku_hantei')->with('data',$data);
    }

    public function hantei(Request $request) {
        $data = array();
        $data['tehai'] = json_decode($request->input('data'), true);
        $data['result'] = $this->yaku_hantei->hantei($data['tehai']);

        return response()->json(json_encode($data, true));
    }
}
