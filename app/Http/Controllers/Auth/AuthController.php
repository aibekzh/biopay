<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;
use Laravel\Passport\Http\Controllers\AccessTokenController;

class AuthController extends AccessTokenController
{
    public function register(Request $request)
    {
        try{
            $validator = Validator::make($request->all(),['name' => 'required','email' => 'required|email','password' => 'required|string|confirmed']);

            if(!$validator->fails()){
                $user           = new User;
                $user->name     = $request->name;
                $user->email    = $request->email;
                $user->password = Hash::make($request->password);
                $user->save();

                return response()->json(
                    [
                        'success' => true,
                        'message' => 'Пользователь успешно зарегистрирован'
                    ], 201,[],JSON_UNESCAPED_UNICODE
                );
            }

            return response()->json(
                [
                    'success' => false,
                    'message' => $validator->errors()
                ], 400
            );

        }catch (\Exception $exception){

            return response()->json(
                [
                    'success' => false,
                    'message' => $exception->getMessage()
                ], 500
            );
        }
    }

    public function logout(Request $request)
    {
        try{
            $request->user()
                ->tokens
                ->each(function ($token, $key) {
                    $this->revokeAccessAndRefreshTokens($token->id);
                });

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Успешный выход из системы',
                ],200,[],JSON_UNESCAPED_UNICODE
            );
        }catch (\Exception $exception){

            return response()->json(
                [
                    'success' => false,
                    'message' => $exception->getMessage(),
                ],500
            );
        }
    }

    public function user(Request $request)
    {
        try{

            return response()->json(
                [
                    'success' => true,
                    'data'    => $request->user(),
                ]
            );
        }catch (\Exception $exception){

            return response()->json(
                [
                    'success' => false,
                    'message' => $exception->getMessage(),
                ],500
            );
        }
    }

    protected function revokeAccessAndRefreshTokens($tokenId) {
        $tokenRepository = app('Laravel\Passport\TokenRepository');
        $refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');

        $tokenRepository->revokeAccessToken($tokenId);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($tokenId);
    }
}