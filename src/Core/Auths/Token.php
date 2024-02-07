<?php

namespace App\Core\Auths;

class Token extends Auth {

    public static function getAllowedTokens() {
        return explode(",", $_ENV["BEARER_TOKENS"]);
    }

    public static function isTokenAllowed($token) {
        return !empty($token) && in_array($token, self::getAllowedTokens());
    }

    public static function authenticate() {
        $token = self::getBearerToken();
        return self::isTokenAllowed($token);
    }
}
