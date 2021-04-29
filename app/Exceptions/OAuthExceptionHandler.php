<?php


namespace App\Exceptions;



use App\Helpers\CookieStorage;
use Laravel\Passport\Exceptions\OAuthServerException;

class OAuthExceptionHandler
{

    private static function getMessage($code) {
        $mappings = [
            2 => [
                "code"      => 400,
                "message"   => "Тип разрешения на авторизацию (authorization grant) не поддерживается сервером авторизации."
            ],
            3 => [
                "code"      => 400,
                "message"   => "В запросе отсутствует обязательный параметр, он включает недопустимое " .
                    "значение параметра, включает параметр более одного раза или имеет другой неправильный формат."
            ],
            4 => [
                "code"      => 400,
                "message"   => "Тип разрешения на авторизацию (authorization grant) не поддерживается сервером авторизации."
            ],
            6 => [
                "code"      => 401,
                "message"   => "Учетные данные пользователя неверны."
            ],
            7 => [
                "code"      => 500,
                "message"   => "Ошибка сервера."
            ],
            8 => [
                "code"      => 401,
                "message"   => "Токен обновления недействителен."
            ],
            9 => [
                "code"      => 401,
                "message"   => "Владелец ресурса или сервер авторизации отклонил запрос."
            ],
            10 => [
                "code"      => 401,
                "message"   => 'Предоставленное разрешение авторизации (например, код авторизации, учетные данные владельца ресурса) или токен обновления '
                    . 'недействителен, истек, отозван, не соответствует URI перенаправления, используемому в запросе авторизации,'
                    . 'или был выдан другому клиенту.'
            ]
        ];

        return $mappings[$code];
    }

    static function handle(OAuthServerException $exception) {
        if ($exception->getCode() == 6 || $exception->getCode() == 10) {
            return self::getMessage(6);
        }

        if ($exception->getCode() == 8) {
            $cookie = new CookieStorage();
            $cookie->delete('access_token');
            $cookie->delete('refresh_token');
        }

        return self::getMessage($exception->getCode());
    }
}
