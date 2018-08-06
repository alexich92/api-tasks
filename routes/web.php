<?php

define("API_VERSION", 'v1');
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

$router->get('/', function () use ($router) {
    return $router->app->version() . ' - ' . 'Current API version: ' . API_VERSION;
});

/** CORS */
$router->options(
    '/{any:.*}', [
    'middleware' => ['cors'],
    function () {
        return response('OK', 200);
    }
]);

/** Routes that doesn't require auth */
$router->group(['namespace' => API_VERSION, 'prefix' => API_VERSION, 'middleware' => 'cors'], function () use ($router) {
    $router->post('/login', ['uses' => 'UserController@login']);
    $router->post('/register', ['uses' => 'UserController@register']);
    $router->post('/forgot-password', ['uses' => 'UserController@forgotPassword']);

});

/** Routes with auth */
$router->group(['namespace' => API_VERSION, 'prefix' => API_VERSION, 'middleware' => 'cors|jwt'], function () use ($router) {

    $router->patch('/update-info', ['uses' => 'UserController@updateInfo']);


        $router->group(['middleware' => 'admin'], function () use ($router) {
            $router->post('/activate', ['uses' => 'UserController@activate']);
            $router->get('/get-all-users', ['uses' => 'UserController@getAllUsers']);
            $router->put('/user/update/{id}', ['uses' => 'UserController@updateUser']);


    });


});