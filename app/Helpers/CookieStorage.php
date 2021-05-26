<?php

namespace App\Helpers;

class CookieStorage
{
    protected $cookie;

    //The setter function for the cookie
    public function set($key, $value, $expire = 900)
    {
        //This will only update the cookie IF it is not already there.
        setcookie($key, $value, time() + 86400, '/');
    }

    //The getter function for the cookie
    public function get($key)
    {
        if (isset($_COOKIE[$key])) {
            $this->cookie = $_COOKIE[$key];

            return $this->cookie;
        } else {
            return false;
        }
    }

    public function delete($key)
    {
        if (isset($_COOKIE[$key])) {
            unset($_COOKIE[$key]);
            setcookie($key, null, -1, '/');

            return true;
        } else {
            return false;
        }
    }
}