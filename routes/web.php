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
    return env('APP_NAME');
});

//$router = app('Dingo\Api\Routing\Router');

    $router->group(['prefix'=>'oauth'],function ($router){
        $router->post('token','\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');
    });


    $router->group(['namespace'=>'Auth','prefix'=>'api'],function ($router) {

        $router->post('user/register', 'AuthController@register');
        $router->post('login', 'AccessController@issueToken');
        $router->post('enterprise/register', 'AuthController@registerEnterprise');
        $router->post('refresh', 'AccessController@refreshToken');

        $router->group(['middleware'=>['auth_partial']],function ($router){
            $router->get('logout','AuthController@logout');
        });

    });
    $router->group(['prefix'=>'api'],function ($router){
        $router->get('biometrics/session','FaceMapController@session');

        $router->group(['middleware'=>['auth:api']], function ($router){

            $router->get('data','EnterpriseController@getData');
            $router->get('enterprise/balance','EnterpriseController@getBalance');
            $router->get('enterprise/income/history','EnterpriseController@getPaymentHistory');

            $router->get('user/balance','UserController@getBalance');
            $router->get('user/payment/history','UserController@getPaymentHistory');
            $router->get('user/top-up/history','UserController@getTopUpHistory');
            $router->post('user/balance/top-up','UserController@topUp');

            $router->post('biometrics/enrollment', 'FaceMapController@enroll');
            $router->post('biometrics/match', 'FaceMapController@match');
            $router->post('pay', 'FaceMapController@pay');
        });
    });


