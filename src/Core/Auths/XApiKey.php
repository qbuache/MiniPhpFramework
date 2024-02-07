<?php

namespace App\Core\Auths;

class XApiKey extends Auth {

    public static function authenticate($header = "X-Api-Key") {
        $allowedApiKeys = self::getEnvValues("API_KEYS");
        $xApiKey = self::getHeader($header);
        return !empty($xApiKey) && in_array($xApiKey, $allowedApiKeys);
    }
}
