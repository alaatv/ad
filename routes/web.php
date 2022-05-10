<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

//$router->get('/phpinfo', function () use ($router) {
//    return phpinfo();
//});
//
//$router->get('/version', function () use ($router) {
//    return $router->app->version();
//});
//
//$router->get('/key', function() {
//    return str_random(32);
//});
//$router->get('/uuid', function() {
//        return Str::uuid();
//});

//$router->get('/', [HomeController::class , 'index']);  Not working in lumen
Route::get('getAd', [HomeController::class, 'getAd']);
Route::post('fetchAd', [HomeController::class, 'fetchAd']);
Route::get('tabligh/{UUID}/click', [HomeController::class, 'adClick'])->name('ad.click');
Route::get('debug', [HomeController::class, 'debug']);
Route::get('test', [HomeController::class, 'adTest']);
