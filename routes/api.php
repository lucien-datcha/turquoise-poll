<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/answer', 'AnswerController@index');

Route::get('/average', 'AnswerController@average');

Route::post('/answer/{color}', 'AnswerController@answer');
