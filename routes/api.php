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

Route::get('/', function(Request $request) {
    return "llego";
});

Route::get('/mail', 'PostController_test@mail');
Route::post('/test', 'PostController_test@store');
Route::post('/', 'PostController@store');
#Route::post('/test', 'PostController_test@store');




Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
