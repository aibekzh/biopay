<?php


namespace App\Http\Controllers;



use App\Helpers\CookieStorage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TestController extends Controller
{
    public function delUser(Request $request) {
        $user_id = Cache::get('access_token/'.$request->bearerToken());
        Cache::forget('access_token/'.$request->bearerToken());
        Cache::forget('user_id/'.$user_id);

        $cookie = new CookieStorage();
        $cookie->delete('access_token');
        $cookie->delete('refresh_token');
        $cookie->delete('token_create_time');

        User::query()->findOrFail($user_id)->delete();

        return true;
    }
}
