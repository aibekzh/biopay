<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\OAuthExceptionHandler;
use App\Helpers\AuthHelper;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Exceptions\OAuthServerException;
use Laravel\Passport\Http\Controllers\HandlesOAuthErrors;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ServerRequestInterface;
use App\Helpers\CookieStorage;


class AccessController extends Controller
{
    use HandlesOAuthErrors;

    /**
     * The authorization server.
     *
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    protected $server;

    /**
     * The token repository instance.
     *
     * @var \Laravel\Passport\TokenRepository
     */
    protected $tokens;

    /**
     * The JWT parser instance.
     *
     * @var \Lcobucci\JWT\Parser
     *
     * @deprecated This property will be removed in a future Passport version.
     */
    protected $jwt;

    /**
     * Create a new controller instance.
     *
     * @param \League\OAuth2\Server\AuthorizationServer $server
     * @param \Laravel\Passport\TokenRepository         $tokens
     * @param \Lcobucci\JWT\Parser                      $jwt
     *
     * @return void
     */
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
     * @OA\Post(
     * path="/api/login",
     * summary="Sign in",
     * description="Login by email, password",
     * operationId="login",
     * tags={"auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"username","password"},
     *       @OA\Property(property="username", type="string", format="email", example="test@test.com"),
     *       @OA\Property(property="password", type="string", format="password", example="secret")
     *    ),
     * ),
     *   @OA\Response(
     *      response=201,
     *       description="Success",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="success", type="boolean", example="true"),
     *          @OA\Property(property="data", type="object", example={"token_type":"Bearer","expires_in": 900, "access_token": "*****", "refresh_token": "*****"}),
     *      )
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * )
     */
    /**
     * @param ServerRequestInterface $request
     * @param Request $req
     * @return JsonResponse
     */
    public function issueToken(ServerRequestInterface $request,Request $req)
    {
        try {
            $auth = (new AuthHelper($this->server, $this->tokens, $this->jwt))->issueToken($request, $req);
            return response()->json(
                [
                    'success' => true,
                    'data'    => json_decode($auth['result']),
                    'message' => "",
                ], $auth['code']
            );
        } catch (\Exception $e) {

            if ($e instanceof OAuthServerException) {
                $message = OAuthExceptionHandler::handle($e);

                return response()->json(
                    [
                        'success' => false,
                        'data'    => "",
                        'message' => $message['message']
                    ], $message['code']
                );
            }

            return response()->json(
                [
                    'success' => false,
                    'data'    => "",
                    'message' => $e->getMessage()
                ], 401
            );
        }
    }

    /**
     * @OA\Post(
     * path="/api/refresh",
     * summary="Sign in",
     * description="Login by refresh token",
     * operationId="refresh",
     * tags={"auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"refresh_token"},
     *       @OA\Property(property="refresh_token", type="string"),
     *    ),
     * ),
     *   @OA\Response(
     *      response=201,
     *       description="Success",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="success", type="boolean", example="true"),
     *          @OA\Property(property="data", type="object", example={"token_type":"Bearer","expires_in": 900, "access_token": "*****", "refresh_token": "*****"}),
     *      )
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * )
     */
    /**
     * @param ServerRequestInterface $request
     * @param Request $req
     * @return JsonResponse
     */
    public function refreshToken(ServerRequestInterface $request,Request $req)
    {
        try {
            $auth = (new AuthHelper($this->server, $this->tokens, $this->jwt))->refreshToken($request, $req);

            return response()->json(
                [
                    'success' => true,
                    'data'    => json_decode($auth['result']),
                    "message" => ""
                ], $auth['code']
            );
        } catch (\Exception $e) {
            if ($e instanceof OAuthServerException) {
                $message = OAuthExceptionHandler::handle($e);

                return response()->json(
                    [
                        'success' => false,
                        'data'    => "",
                        'message' => $message['message']
                    ], $message['code']
                );
            }

            return response()->json(
                [
                    'success'   => false,
                    'data'      => "",
                    'message'   => $e->getMessage()
                ], 401
            );
        }
    }


    /**
     * @OA\Get (
     * path="/api/access",
     * summary="Access",
     * description="Check if token is alive",
     * operationId="refresh",
     * tags={"access"},
     * security={ {"bearer": {} }},
     *   @OA\Response(
     *      response=200,
     *       description="Success, returns user's id",
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * )
     */
    public function check(Request $request) {

        return response()->json(
            [
                "success"   => true,
                "data"      => ["user_id" => $request->user_id],
                "message"   => ""
            ]
        );
    }

    /**
     * @OA\Get(
     * path="/api/access/cookie",
     * summary="Access",
     * description="Check token is alive from cookie",
     * operationId="check.cookie",
     * tags={"access"},
     * security={ {"bearer": {} }},
     *   @OA\Response(
     *      response=200,
     *       description="Success, returns user's id",
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * )
     */
    public function getCookie(Request $request): JsonResponse
    {
        $cookie = new CookieStorage();
        $diff = Carbon::parse($cookie->get('token_create_time'))->diffInSeconds(Carbon::now());

        return response()->json(
            [
                "success"   => true,
                "data"      => [
                    "expire_in" => $diff,
                    "access_token" => $cookie->get('access_token'),
                    "refresh_token" => $cookie->get('refresh_token'),
                ],
                "message"   => ""
            ]
        );
    }
}
