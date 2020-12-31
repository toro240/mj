<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'IndexController@show');
Route::get('get_haipai', 'IndexController@get_haipai');
Route::post('get_hai', 'IndexController@get_hai');
Route::post('remove_hai', 'IndexController@remove_hai');
Route::post('cpu_remove', 'IndexController@cpu_remove');

Route::get('yaku_hantei', 'YakuHanteiController@show');
Route::post('yaku_hantei/hantei', 'YakuHanteiController@hantei');

