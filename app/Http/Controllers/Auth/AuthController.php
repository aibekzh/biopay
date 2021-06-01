<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\AuthHelper;
use App\Models\EnterpriseData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends AccessTokenController
{

    /**
     * @OA\Post(
     ** path="/api/user/register",
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
     *      name="phone_number",
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
    public function register(Request $request, ServerRequestInterface $req)
    {
            $validator = Validator::make($request->all(),[
                'name' => 'required',
                'phone_number' => 'required|string|unique:users,email',
                'password' => 'required|string'
            ], [
                'required' => ':attribute должно быть заполнено.',
                'string' => ':attribute является строкой.',
                'phone_number' => ':attribute должен быть действительным номером телефона.',
                'max' => ':attribute не может содержать больше :max символов',
                'unique' => ':attribute уже занята!',
                'min' => ':attribute должен содержать не менее :min символов.',
                'regex' => ':attribute должен состоять из восьми или более символов латинского алфавита, содержать заглавные и строчные буквы, цифры',
                'confirmed' => ':attribute не совпадает.',

            ], [
                'name' => 'Имя',
                'phone_number' => 'Номер телефона',
                'password' => 'Пароль'
            ]);

            if(!$validator->fails()){
                $user           = new User;
                $user->email     = $request->phone_number;
                $user->name    = $request->name;
                $user->password = Hash::make($request->password);
                $user->balance = 0;
                $user->type = 'individual';
                $user->save();
//                try{

//                    if(config('app.env') != 'testing') {
//                        $apiService = new UsersApiRepository();
//                        $apiService->bindBaseRate($user->id);
//                    }

                    $request->merge([
                        "username" => $user->email
                    ]);

                    $auth = (new AuthHelper($this->server, $this->tokens, $this->jwt))->issueToken($req, $request);

                    $request->merge(
                        [
                            'refresh_token' => json_decode($auth['result'])->refresh_token
                        ]
                    );

//                    $user->sendEmailVerificationNotification();
//                }catch (\Exception $exception){
//                    $user->delete();
//
//                    if ($exception instanceof OAuthServerException) {
//                        $message = OAuthExceptionHandler::handle($exception);
//
//                        return response()->json(
//                            [
//                                'success' => false,
//                                'data'    => "",
//                                'message' => $message['message']
//                            ], $message['code']
//                        );
//                    }
//
//                    return response()->json(
//                        [
//                            'success' => false,
//                            'data'    => "",
//                            'message' => $exception->getMessage()
//                        ], 500
//                    );
//                }

                return response()->json(
                    [
                        'success' => true,
                        'data'    => json_decode($auth['result']),
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
     * @OA\Post(
     ** path="/api/enterprise/register",
     *   tags={"auth"},
     *   summary="Sign up",
     *   operationId="eregister",
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
     *      name="phone_number",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="bank_account_number",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="biin",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="integer",format="int64"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="bik",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="address",
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
    public function registerEnterprise(Request $request, ServerRequestInterface $req)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'phone_number' => 'required|string|unique:users,email',
            'password' => 'required|string'
        ], [
                                         'required' => ':attribute должно быть заполнено.',
                                         'string' => ':attribute является строкой.',
                                         'phone_number' => ':attribute должен быть действительным номером телефона.',
                                         'max' => ':attribute не может содержать больше :max символов',
                                         'unique' => ':attribute уже занята!',
                                         'min' => ':attribute должен содержать не менее :min символов.',
                                         'regex' => ':attribute должен состоять из восьми или более символов латинского алфавита, содержать заглавные и строчные буквы, цифры',
                                         'confirmed' => ':attribute не совпадает.',

                                     ], [
                                         'name' => 'Имя',
                                         'phone_number' => 'Номер телефона',
                                         'password' => 'Пароль'
                                     ]);

        if(!$validator->fails()){
            $user           = new User;
            $user->email     = $request->phone_number;
            $user->name    = $request->name;
            $user->password = Hash::make($request->password);
            $user->balance = 0;
            $user->type = 'enterprise';
            $user->save();
            $enterpriseData = new EnterpriseData();
            $enterpriseData->bank_account_number = $request->bank_account_number;
            $enterpriseData->biin = $request->biin;
            $enterpriseData->bik = $request->bik;
            $enterpriseData->address = $request->address;
            $enterpriseData->user_id = $user->id;
            $enterpriseData->save();
//                try{

//                    if(config('app.env') != 'testing') {
//                        $apiService = new UsersApiRepository();
//                        $apiService->bindBaseRate($user->id);
//                    }

            $request->merge([
                                "username" => $user->email
                            ]);

            $auth = (new AuthHelper($this->server, $this->tokens, $this->jwt))->issueToken($req, $request);

            $request->merge(
                [
                    'refresh_token' => json_decode($auth['result'])->refresh_token
                ]
            );

//                    $user->sendEmailVerificationNotification();
//                }catch (\Exception $exception){
//                    $user->delete();
//
//                    if ($exception instanceof OAuthServerException) {
//                        $message = OAuthExceptionHandler::handle($exception);
//
//                        return response()->json(
//                            [
//                                'success' => false,
//                                'data'    => "",
//                                'message' => $message['message']
//                            ], $message['code']
//                        );
//                    }
//
//                    return response()->json(
//                        [
//                            'success' => false,
//                            'data'    => "",
//                            'message' => $exception->getMessage()
//                        ], 500
//                    );
//                }

            return response()->json(
                [
                    'success' => true,
                    'data'    => json_decode($auth['result']),
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

            DB::table('user_access_tokens')->where('access_token',$request->bearerToken())->delete();
//            Cache::forget("access_token/".$request->bearerToken());
//            Cache::forget("user_id/".$request->user()->id);
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


//    public function user(Request $request)
//    {
//        try{
//
//            return response()->json(
//                [
//                    'success' => true,
//                    'data'    => $request->user(),
//                    'message' => "",
//                ]
//            );
//        }catch (\Exception $exception){
//
//            return response()->json(
//                [
//                    'success' => false,
//                    'data'    => false,
//                    'message' => $exception->getMessage(),
//                ],500
//            );
//        }
//    }

    protected function revokeAccessAndRefreshTokens($tokenId) {
        $tokenRepository = app('Laravel\Passport\TokenRepository');
        $refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');

        $tokenRepository->revokeAccessToken($tokenId);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($tokenId);
    }
}
