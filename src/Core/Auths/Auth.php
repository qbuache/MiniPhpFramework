<?php

namespace App\Core\Auths;

abstract class Auth {

    public static function getBasicAuth() {
        return (object)[
            "username" => $_SERVER["PHP_AUTH_USER"] ?? null,
            "password" => $_SERVER["PHP_AUTH_PW"] ?? null,
        ];
    }

    public static function getBasicAuthUser() {
        return $_SERVER["PHP_AUTH_USER"] ?? null;
    }

    /**
     * @return string|false
     */
    public static function getBearerToken() {
        $authorizationHeader = self::getHeader("Authorization");
        return substr($authorizationHeader ?? null, 7);
    }

    public static function getHeader($header, $default = false) {
        return getallheaders()[$header] ?? $default;
    }

    public static function getEnvValues($key, $separator = ",") {
        return explode($separator, $_ENV[$key]);
    }
}
