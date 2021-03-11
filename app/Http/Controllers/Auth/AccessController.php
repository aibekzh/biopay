<?php

namespace App\Http\Controllers\Auth;

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
use App\Http\Controllers\Auth\AuthController;
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
     *       required={"grant_type","client_id","client_secret"},
     *       @OA\Property(property="username", type="string", format="email", example="test@test.com"),
     *       @OA\Property(property="password", type="string", format="password", example="secret"),
     *       @OA\Property(property="grant_type", type="string", example="password"),
     *       @OA\Property(property="client_id", type="integer", example="2"),
     *       @OA\Property(property="client_secret", type="string"),
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
    public function issueToken(ServerRequestInterface $request,Request $req)
    {
        try {
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

            return response()->json(
                [
                    'success' => true,
                    'data'    => json_decode($result->content()),
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
}
