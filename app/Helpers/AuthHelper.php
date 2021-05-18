<?php


namespace App\Helpers;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\UnauthorizedException;
use Laravel\Passport\Http\Controllers\HandlesOAuthErrors;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;
use League\OAuth2\Server\AuthorizationServer;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;

class AuthHelper
{
    use HandlesOAuthErrors;

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

    public function refreshToken(ServerRequestInterface $request,Request $req) {
        if(!$req->has('refresh_token')){
            if ($req->cookie('refresh_token') != null) {
                $req->merge([
                    'refresh_token'=>$req->cookie('refresh_token')
                ]);
            }
        }

        $validator = Validator::make($req->all(),[
            'refresh_token' => 'required|exists:user_refresh_tokens,refresh_token'
        ]);

        if($validator->fails()){
            throw new UnauthorizedException('Refresh токен не был задан или недоступен');
        }

        $request = $request->withParsedBody(
            [
                'refresh_token'=>$req->refresh_token,
                'client_secret' => env('CLIENT_SECRET'),
                'client_id' => env('CLIENT_ID'),
                'grant_type' => 'refresh_token'
            ]
        );

        return $this->authorization($request,$req);
    }

    public function issueToken(ServerRequestInterface $request,Request $req) {
            $request = $request->withParsedBody(
                [
                    'username'=>$req->username,
                    'password'=>$req->password,
                    'client_secret' => env('CLIENT_SECRET'),
                    'client_id' => env('CLIENT_ID'),
                    'grant_type' => 'password'
                ]
            );

            return $this->authorization($request,$req);
    }

    public function authorization($request,$req){

        $result = $this->withErrorHandling(
            function () use ($request) {
                return $this->convertResponse(
                    $this->server->respondToAccessTokenRequest($request, new Psr7Response)
                );
            }
        );
        $partial = false;
        $access_token = json_decode($result->content())->access_token;
        $refresh_token = json_decode($result->content())->refresh_token;
        $req->headers->set('Authorization', 'Bearer ' .$access_token);

        DB::table('user_refresh_tokens')->updateOrInsert(
            [
                'user_id' => $req->user()->id
            ],
            [
                'refresh_token' => $refresh_token
            ]
        );


        if(config('app.env') != 'testing'){
            $cookie = new CookieStorage();
            $cookie->set('token_create_time', Carbon::now()->addSeconds(900)->toDateTimeString());
            $cookie->set('access_token', $access_token);
            $cookie->set('refresh_token', $refresh_token);

            if (!is_null(\request()->user()->email_verified_at)) {
                Cache::put("access_token/$access_token", $req->user()->id, Carbon::now()->addMinutes(env('TOKEN_EXPIRE_IN', 15)));
                Cache::put("user_id/".$req->user()->id, $access_token, Carbon::now()->addMinutes(env('TOKEN_EXPIRE_IN', 15)));

            } else $partial = true;
        }

        return [
            "result" => $result->content(),
            "code"   => $partial ? 206 : 200
        ];
    }
}
