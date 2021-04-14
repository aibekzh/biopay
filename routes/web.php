<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->post('password/reset',
           [
               'as'=>'password.reset',
               'uses'=>'Auth\ForgotPasswordController@reset',
           ]
);
$router->get('/', function () use ($router) {
    return $router->app->version();
});

$api = app('Dingo\Api\Routing\Router');
$api->version('v1',function ($api){
    $api->group(['prefix'=>'oauth'],function ($api){
        $api->post('token','\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');
    });
    $api->post('register','App\Http\Controllers\Auth\AuthController@register');
    $api->post('login','App\Http\Controllers\Auth\AccessController@issueToken');
    $api->post('refresh','App\Http\Controllers\Auth\AccessController@refreshToken');
    $api->post('password/email',
               [
                'as'=>'password.email',
                'uses'=>'App\Http\Controllers\Auth\ForgotPasswordController@forgot',
               ]);

    $api->post('access', 'App\Http\Controllers\Auth\AccessController@check');

    $api->get('email/verify/{id}',
                [
                    'as'   => 'verification.verify',
                    'uses' => 'App\Http\Controllers\Auth\VerificationController@verify',
                ]);

    $api->group(['namespace'=>'App\Http\Controllers','middleware'=>['auth:api']],function ($api){

        $api->get('email/resend','Auth\VerificationController@resend');
        $api->get('check','Auth\AuthController@user');
        $api->get('logout','Auth\AuthController@logout');
    });

});
