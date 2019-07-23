<?php

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
$router->get('/uuid', function() {
        return Str::uuid();
});

//$router->get('/', [HomeController::class , 'index']);  Not working in lumen
$router->get('/', 'HomeController@index');
$router->post('/fetchAd', 'HomeController@fetchAds');
$router->get('ad/{UUID}/click', 'HomeController@adClick');
