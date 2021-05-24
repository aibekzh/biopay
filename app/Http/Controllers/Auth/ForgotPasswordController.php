<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\DontMatchOldPassword;
use App\Rules\MatchCurrentPassword;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    /**
     * @OA\Post (
     *   path="/api/password/email",
     *   operationId="api/password/email",
     *   tags={"auth"},
     *   summary="Send message to reset password",
     *   description="Send message to reset password",
     *
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="success", type="boolean", example="true"),
     *          @OA\Property(property="message", type="string", example="Мы отправили письмо на Ваш почтовый ящик с инструкцией по сбросу пароля!"),
     *      )
     *   ),
     *   @OA\Response(
     *      response=412,
     *      description="Precondition Failed",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="success", type="boolean", example="true"),
     *          @OA\Property(property="message", type="string", example="Почта не найдена!"),
     *      )
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Validation Failed",
     *   ),
     *)
     **/
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forgot(Request $request) {
        try {
            $validator = Validator::make($request->all(),['email' => 'required|email']);

            if(!$validator->fails()){
                $response = $this->broker()->sendResetLink(
                    $this->credentials($request)
                );

                return response()->json(
                    [
                        "success" => true,
                        "data"    => $response == Password::RESET_LINK_SENT
                            ? $this->sendResetLinkResponse($request, $response)
                            : $this->sendResetLinkFailedResponse($request, $response),
                        "message" => ""
                    ]
                );
            }

            return response()->json(
                [
                    "success" => false,
                    "data"    => "",
                    'message' => $validator->errors()
                ],400
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
     * @OA\Post (
     *   path="/api/password/reset",
     *   operationId="password/reset",
     *   tags={"auth"},
     *   summary="Reset the Password",
     *   description="Reset the password",
     *
     *   @OA\Parameter(
     *      name="token",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
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
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="success", type="boolean", example="true"),
     *          @OA\Property(property="message", type="string", example="Пароль успешно изменен"),
     *      )
     *   ),
     *   @OA\Response(
     *      response=412,
     *      description="Precondition Failed",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="success", type="boolean", example="true"),
     *          @OA\Property(property="message", type="string", example="Этот токен сброса пароля недействителен."),
     *      )
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Validation Failed",
     *   ),
     *)
     **/
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request) {
        try {
            $validator = Validator::make($request->all(),
                                         [
                                            'email'    => 'required|email',
                                            'token'    => 'required|string',
                                            'password' => 'required|string|confirmed'
                                         ]
            );

            if(!$validator->fails()){
                $reset_password_status = Password::reset($request->all(), function ($user, $password) {
                    $user->password = Hash::make($password);
                    $user->save();
                });

                if ($reset_password_status == Password::INVALID_TOKEN) {

                    return response()->json(
                        [
                            "success" => false,
                            "data"    => "",
                            "message" => trans('passwords.token'),
                        ], 412,[],JSON_UNESCAPED_UNICODE
                    );
                }

                return response()->json(
                    [
                        "success" => true,
                        "data"    => "",
                        "message" => trans('passwords.changed'),
                    ],200,[],JSON_UNESCAPED_UNICODE
                );
            }

            return response()->json([
                                        "success" => false,
                                        "data"    => "",
                                        "message" => $validator->errors(),
                                    ],400
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
     * @OA\Post (
     *   path="/api/password/change",
     *   operationId="password/change",
     *   tags={"auth"},
     *   summary="Change the password",
     *   description="Change the password",
     *   security={ {"bearer": {} }},
     *
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="new_password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="new_password_confirmation",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *   ),
     *   @OA\Response(
     *      response=412,
     *      description="Precondition Failed",
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Validation Failed",
     *   ),
     *)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function change(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', new MatchCurrentPassword],
            'new_password' => ['required', 'confirmed', new DontMatchOldPassword],
        ],
        [
            "new_password.confirmed" => "Пароли не совпадают."
        ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "data"    => "",
                    "message" => $validator->errors()
                ], 400
            );
        }

        try {
            $user = User::query()->findOrFail(auth()->user()->id);
            $user->password = Hash::make($request->new_password);
            $user->saveOrFail();

            return response()->json(
                [
                    "success" => true,
                    "data"    => "",
                    "message" => "Пароль успешно был изменен"
                ]
            );
        } catch (\Exception $exception) {
            return response()->json(
                [
                    "success" => false,
                    "data"    => "",
                    "message" => $exception->getMessage()
                ], 500
            );
        }

    }

    /**
     * Get the needed authentication credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only('email');
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return array
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return
            [
                'success' => true,
                'data'    => "",
                'message' => trans($response),
            ];
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return array
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        throw new ValidationException(trans($response));
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }
}
