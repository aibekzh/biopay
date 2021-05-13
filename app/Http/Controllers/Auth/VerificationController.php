<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\AuthHelper;
use App\Helpers\CookieStorage;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ServerRequestInterface;


class VerificationController extends Controller
{

    private $server;
    private $jwt;
    private $tokens;

    public function __construct(
        AuthorizationServer $server,
        TokenRepository $tokens,
        JwtParser $jwt
    ) {
        $this->jwt    = $jwt;
        $this->server = $server;
        $this->tokens = $tokens;
    }

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
     *      name="refresh_token",
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
     *      response=200,
     *      description="Full login",
     *   ),
     *   @OA\Response(
     *      response=206,
     *      description="Partial login",
     *   ),
     *)
     **/
    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify($id, Request $request, ServerRequestInterface $requestInferface) {
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

            $auth = (new AuthHelper($this->server, $this->tokens, $this->jwt))->refreshToken($requestInferface, $request);
            return response()->json(
                [
                    "success" => true,
                    "data"    => json_decode($auth['result']),
                    "message" => trans('verify.success')
                ], $auth['code'],[],JSON_UNESCAPED_UNICODE
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
            \request()->merge([
                "refresh_token" => (new CookieStorage())->get('refresh_token')
            ]);

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
