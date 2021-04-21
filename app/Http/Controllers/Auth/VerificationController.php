<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class VerificationController extends Controller
{

    /**
     * @OA\Get (
     *   path="/api/email/verify/{id}",
     *   operationId="verify",
     *   tags={"auth"},
     *   summary="Verify the User",
     *   description="Verify the user",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="access_token",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="expires",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="hash",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="signature",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=202,
     *      description="Success",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="success", type="boolean", example="true"),
     *          @OA\Property(property="message", type="string", example="Ваша учетная запись успешно подтверждена"),
     *      )
     *   ),
     *   @OA\Response(
     *      response=410,
     *      description="verify invalid",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="success", type="boolean", example="true"),
     *          @OA\Property(property="message", type="string", example="Указан недействительный / просроченный URL"),
     *      )
     *   ),
     *)
     **/
    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify($id, Request $request) {
        try{

            if (!app('api.url')->version('v1')->hasValidSignature($request)) {

                return response()->json(
                    [
                        "success" => false,
                        "data"    => "",
                        "message" => trans('verify.invalid')
                    ], 410,[],JSON_UNESCAPED_UNICODE
                );
            }
            $user = User::findOrFail($id);

            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            return response()->json(
                [
                    "success" => true,
                    "data"    => "",
                    "message" => trans('verify.success')
                ], 202,[],JSON_UNESCAPED_UNICODE
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
     * @OA\Get (
     *   path="/api/email/resend",
     *   operationId="email/resend",
     *   tags={"auth"},
     *   security={ {"Bearer": {} }},
     *   summary="Send verify message to the User",
     *   description="Send verify message to the user",
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="success", type="boolean", example="true"),
     *          @OA\Property(property="message", type="string", example="Мы отправили письмо на Ваш почтовый ящик с инструкцией по подтверждению почты!"),
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
     *   )
     *),
     *)
     **/
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend() {
        try{

            if (auth()->user()->hasVerifiedEmail()) {

                return response()->json(
                    [
                        "success" => false,
                        "data"    => "",
                        "message" => trans('verify.verified'),
                    ], 409,[],JSON_UNESCAPED_UNICODE);
            }
            auth()->user()->sendEmailVerificationNotification();

            return response()->json(
                [
                    "success" => true,
                    "data"    => "",
                    "message" => trans('verify.sent'),
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
}
