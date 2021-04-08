<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\AuthController;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Http\Controllers\HandlesOAuthErrors;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use mysql_xdevapi\Exception;
use Nyholm\Psr7\Response as Psr7Response;
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function issueToken(ServerRequestInterface $request,Request $req)
    {
        try {
            $request = $request->withParsedBody(
                [
                    'username'=>$req->username,
                    'password'=>$req->password,
                    'client_secret' => env('CLIENT_SECRET'),
                    'client_id' => env('CLIENT_ID'),
                    'grant_type' => 'password'
                ]
            );

            return response()->json(
                [
                    'success' => true,
                    'data'    => json_decode($this->authorization($request,$req)),
                ]
            );
        } catch (\Exception $e) {

            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage()], 401
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(ServerRequestInterface $request,Request $req)
    {
        try {
            $request = $request->withParsedBody(
                [
                    'refresh_token'=>$req->refresh_token,
                    'client_secret' => env('CLIENT_SECRET'),
                    'client_id' => env('CLIENT_ID'),
                    'grant_type' => 'refresh_token'
                ]
            );
            return response()->json(
                [
                    'success' => true,
                    'data'    => json_decode($this->authorization($request,$req)),
                ]
            );
        } catch (\Exception $e) {

            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage()], 401
            );
        }
    }

    public function authorization($request,$req){

        $result = $this->withErrorHandling(
            function () use ($request) {
                return $this->convertResponse(
                    $this->server->respondToAccessTokenRequest($request, new Psr7Response)
                );
            }
        );
        $access_token = json_decode($result->content())->access_token;
        $refresh_token = json_decode($result->content())->refresh_token;
        $check = new AuthController($this->server,$this->tokens,$this->jwt);
        $req->headers->set('Authorization', 'Bearer ' .$access_token);
        $user = json_decode($check->user($req)->content())->data;
        if(config('app.env') != 'testing'){
            Cache::put($access_token, ['id'=>$user->id,'email'=>$user->email], Carbon::now()->addMinutes(env('TOKEN_EXPIRE_IN', 15)));
            $cookie = new CookieStorage();
            $cookie->set('access_token', $access_token);
            $cookie->set('refresh_token', $refresh_token);
        }

        return $result->content();
    }
}
