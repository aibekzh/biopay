<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Repository\UsersApiRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Http\Controllers\AccessTokenController;

class AuthController extends AccessTokenController
{

    /**
     * @OA\Post(
     ** path="/api/register",
     *   tags={"auth"},
     *   summary="Sign up",
     *   operationId="register",
     *
     *  @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *  @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password_confirmation",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=201,
     *       description="Success",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="success", type="boolean", example="true"),
     *          @OA\Property(property="message", type="string", example="Пользователь успешно зарегистрирован"),
     *      )
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *)
     **/
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
            $validator = Validator::make($request->all(),[
                'name' => 'required',
                'email' => 'required|string|email:rfc,dns|max:255|unique:users',
                'password' => 'required|string|min:8|regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$/|confirmed'
            ], [
                'required' => 'Поле :attribute должно быть заполнено.',
                'string' => 'Поле :attribute является строкой.',
                'email' => 'Поле :attribute должен быть действительным адресом электронной почты.',
                'max' => 'Поле :attribute не может содержать больше :max символов',
                'unique' => ':attribute уже занята!',
                'min' => 'Поле :attribute должен содержать не менее :min символов.',
                'regex' => 'Пароль должен состоять из восьми или более символов латинского алфавита, содержать заглавные и строчные буквы, цифры',
                'confirmed' => 'Пороль не совпадает.',

            ]);

            if(!$validator->fails()){
                $user           = new User;
                $user->name     = $request->name;
                $user->email    = $request->email;
                $user->password = Hash::make($request->password);
                $user->save();

                try{

                    if(config('app.env') != 'testing') {
                        $apiService = new UsersApiRepository();
                        $apiService->bindBaseRate($user->id);
                    }
                }catch (\Exception $exception){
                    $user->delete();
                    return response()->json(
                        [
                            'success' => false,
                            'data'    => "",
                            'message' => $exception->getMessage()
                        ], 500
                    );
                }

                return response()->json(
                    [
                        'success' => true,
                        'data'    => "",
                        'message' => 'Пользователь успешно зарегистрирован'
                    ], 201,[],JSON_UNESCAPED_UNICODE
                );
            }

            return response()->json(
                [
                    'success' => false,
                    'data'    => "",
                    'message' => $validator->errors()
                ], 400
            );
    }

    /**
     * @OA\Get(
     *   path="/api/logout",
     *   operationId="logout",
     *   tags={"auth"},
     *   security={ {"bearer": {} }},
     *   summary="Logout",
     *   description="Logout user",
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="success", type="boolean", example="true"),
     *          @OA\Property(property="message", type="string", example="Успешный выход из системы"),
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *      @OA\JsonContent(
     *          type = "object",
     *          @OA\Property(property="success", type="boolean", example="false"),
     *          @OA\Property(property="message", type="string", example="Вы не авторизованы"),
     *      ),
     *   ),
     *)
     **/
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try{
            $request->user()
                ->tokens
                ->each(function ($token, $key) {
                    $this->revokeAccessAndRefreshTokens($token->id);
                });

            Cache::forget("access_token/".$request->bearerToken());
            Cache::forget("user_id/".$request->user()->id);
            return response()->json(
                [
                    'success' => true,
                    'data'    => "",
                    'message' => 'Успешный выход из системы',
                ],200,[],JSON_UNESCAPED_UNICODE
            );
        }catch (\Exception $exception){

            return response()->json(
                [
                    'success' => false,
                    'data'    => "",
                    'message' => $exception->getMessage(),
                ],500
            );
        }
    }

    /**
     * @OA\Get(
     *   path="/api/check",
     *   operationId="check",
     *   tags={"auth"},
     *   security={ {"bearer": {} }},
     *   summary="Check the User",
     *   description="Check",
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *          type = "object",
     *          @OA\Property(property="success", type="boolean", example="true"),
     *          @OA\Property(property="data", type="object", example={"id":354,"name": "test", "email": "test@test.com", "email_verified_at": null, "balance": 0, "remember_token": null, "created_at": "2021-02-24T03:30:57.000000Z","updated_at": "2021-02-24T03:30:57.000000Z"}),
     *      ),
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *      @OA\JsonContent(
     *          type = "object",
     *          @OA\Property(property="success", type="boolean", example="false"),
     *          @OA\Property(property="message", type="string", example="Вы не авторизованы"),
     *      ),
     *   ),
     *)
     **/
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        try{

            return response()->json(
                [
                    'success' => true,
                    'data'    => $request->user(),
                    'message' => "",
                ]
            );
        }catch (\Exception $exception){

            return response()->json(
                [
                    'success' => false,
                    'data'    => false,
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
