<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use mysql_xdevapi\Exception;

class ForgotPasswordController extends Controller
{
    public function forgot(Request $request) {
        try {
            $validator = Validator::make($request->all(),['email' => 'required|email']);

            if(!$validator->fails()){
                $response = $this->broker()->sendResetLink(
                    $this->credentials($request)
                );

                return $response == Password::RESET_LINK_SENT
                    ? $this->sendResetLinkResponse($request, $response)
                    : $this->sendResetLinkFailedResponse($request, $response);
            }

            return response()->json(
                [
                    "success" => false,
                    'message' => $validator->errors()
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
                            "message" => trans('passwords.token'),
                        ], 400,[],JSON_UNESCAPED_UNICODE
                    );
                }

                return response()->json(
                    [
                        "success" => true,
                        "message" => trans('passwords.changed'),
                    ],200,[],JSON_UNESCAPED_UNICODE
                );
            }

            return response()->json([
                                        "success" => false,
                                        "message" => $validator->errors(),
                                    ]);
        }catch (\Exception $exception){

            return response()->json(
                [
                    'success' => false,
                    'message' => $exception->getMessage(),
                ],500
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
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return response()->json(
            [
                'success' => true,
                'message' => trans($response),
            ],200,[],JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return response()->json(
            [
                "success" => false,
                "message" => trans($response)
            ],200,[],JSON_UNESCAPED_UNICODE
        );
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
