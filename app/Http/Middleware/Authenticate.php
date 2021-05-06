<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Helpers\CookieStorage;
use Illuminate\Support\Facades\Cache;
use function PHPUnit\Framework\isNull;


class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Contracts\Auth\Factory $auth
     *
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (!$this->checkAuth($request)) {
                $this->cookieDelete("access_token");

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Вы не авторизованы',
                    ], 401
                );
            }

            return $next($request);
        } catch (\Exception $exception) {
            $this->cookieDelete("access_token");

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Вы не авторизованы',
                ], 401
            );
        }


    }

    static function cookieDelete($key)
    {
        $cookie = new CookieStorage();
        $cookie->delete($key);
    }

    static function checkAuth($request): bool
    {
        $bearer = $request->bearerToken();

        if (is_null($bearer)) {

            if ($request->cookie('access_token') != null) {
                $request->headers->set('Authorization', 'Bearer ' . $request->cookie('access_token'));
                $bearer = $request->bearerToken();
            }else{
                return false;
            }
        }

        $user_id        = Cache::get("access_token/".$bearer);
        $second_token   = Cache::get("user_id/".$user_id);
        if (is_null($bearer) || $bearer != $second_token) return false;

        $request->merge(["user_id" => $user_id]);
        return true;
    }
}
