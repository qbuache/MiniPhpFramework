<?php

namespace App\Core;

use App\Core\Auths\Ldap;
use App\Core\Auths\Token;
use App\Core\Auths\XApiKey;
use App\Exceptions\SystemException;

class Authenticate {

    public static function with($type) {
        if (!empty($type)) {
            switch (strtolower($type)) {
                case "ldap":
                    return Ldap::authenticate();
                case "token":
                    return Token::authenticate();
                case "xapikey":
                    return XApiKey::authenticate();
                default:
                    throw new SystemException("No such auth method", 500);
            }
        }
        return true;
    }
}
