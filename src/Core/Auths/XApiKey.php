<?php

namespace App\Core\Auths;

class XApiKey extends Auth {

    public static function authenticate() {
        $allowedApiKeys = self::getEnvValues("API_KEYS");
        $xApiKey = self::getXApiKey();
        return !empty($xApiKey) && in_array($xApiKey, $allowedApiKeys);
    }
}
