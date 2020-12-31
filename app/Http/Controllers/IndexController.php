<?php

namespace App\Http\Controllers;

use App\Index;
use Illuminate\Http\Request;


class IndexController extends Controller
{

    private $index;

    function __construct()
    {
        $this->index = new Index();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Index  $index
     * @return \Illuminate\Http\Response
     */
    public function show(Index $index)
    {
        return view('index', array());
    }
    
    public function get_haipai() {
        $this->index->init();
        $index = array(
            'user_haipai' => $this->index->get_user_haipai(),
            'simo_haipai' => $this->index->get_simo_haipai(),
            'toi_haipai' => $this->index->get_toi_haipai(),
            'kami_haipai' => $this->index->get_kami_haipai(),
            'other_info' => $this->index->get_other_info(),
            'yama' => $this->index->get_yama(), // TODO: DB化
            'kawa' => array(
                'user_kawa' => array(),
                'simo_kawa' => array(),
                'toi_kawa' => array(),
                'kami_kawa' => array(),
            ),
        );

        return response()->json(json_encode($index, true));
    }

    public function get_hai(Request $request) {
        $data = json_decode($request->input('data'), true);
        $data = $this->index->get_hai($data);

        return response()->json(json_encode($data, true));
    }

    public function remove_hai(Request $request) {
        $data = json_decode($request->input('data'), true);
        $data = $this->index->remove_hai($data);

        return response()->json(json_encode($data, true));
    }

    public function cpu_remove(Request $request) {
        $data = json_decode($request->input('data'), true);
        $data = $this->index->cpu_remove($data);

        return response()->json(json_encode($data, true));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Index  $index
     * @return \Illuminate\Http\Response
     */
    public function edit(Index $index)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Index  $index
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Index $index)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Index  $index
     * @return \Illuminate\Http\Response
     */
    public function destroy(Index $index)
    {
        //
    }
}
