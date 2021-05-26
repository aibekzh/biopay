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

$router->post('api/password/reset',
           [
               'as'=>'password.reset',
               'uses'=>'Auth\ForgotPasswordController@reset',
           ]
);

$router->delete('api/user/self',
    [
        'as'=>'user.delete',
        'uses'=>'TestController@delUser',
    ]
);

$router->get('/', function () use ($router) {
    return $router->app->version();
});

//$router = app('Dingo\Api\Routing\Router');

    $router->group(['prefix'=>'oauth'],function ($router){
        $router->post('token','\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');
    });
    $router->post('register','App\Http\Controllers\Auth\AuthController@register');
    $router->post('login','App\Http\Controllers\Auth\AccessController@issueToken');
    $router->post('refresh','App\Http\Controllers\Auth\AccessController@refreshToken');
    $router->post('password/email',
               [
                'as'=>'password.email',
                'uses'=>'App\Http\Controllers\Auth\ForgotPasswordController@forgot',
               ]);

    $router->get('email/verify/{id}',
                [
                    'as'   => 'verification.verify',
                    'uses' => 'App\Http\Controllers\Auth\VerificationController@verify',
                ]);

    $router->group(['namespace'=>'App\Http\Controllers','middleware'=>['auth:api']],function ($router){
        $router->get('access/cookie', 'Auth\AccessController@getCookie');
        $router->get('access', 'Auth\AccessController@check');
        $router->get('check','Auth\AuthController@user');
        $router->post('password/change', 'Auth\ForgotPasswordController@change');
    });

    $router->group(['namespace'=>'App\Http\Controllers','middleware'=>['auth_partial']],function ($router){
        $router->get('email/resend','Auth\VerificationController@resend');
        $router->get('logout','Auth\AuthController@logout');
    });
