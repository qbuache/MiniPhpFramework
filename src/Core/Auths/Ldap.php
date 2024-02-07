<?php

namespace App\Core\Auths;

use App\Exceptions\ApiException;
use App\Exceptions\SystemException;

class Ldap extends Auth {

    public static function authenticate() {
        $credentials = self::getBasicAuth();
        if (empty($credentials->username) || empty($credentials->password)) {
            throw new ApiException("Missing basic auth credentials", 400);
        }

        $ldapConn = @ldap_connect("ldaps://" . config("ldap.server") . ":" . config("ldap.port"));
        if ($ldapConn === false) {
            throw new SystemException("Could not connect to LDAP server", 500);
        }

        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);

        $ldapBind = @ldap_bind($ldapConn, "uid={$credentials->username},ou=eliot,ou=users,ou=gest,ou=ug,dc=unil,dc=ch", $credentials->password);
        if ($ldapBind === false) {
            self::disconnect($ldapConn);
            throw new ApiException("Wrong username or password", 401);
        }

        $ldapAuthGroup = $_ENV["LDAP_AUTH_GROUP"];
        $searchResult = ldap_search(
            $ldapConn,
            "uid={$credentials->username},ou=eliot,ou=users,ou=gest,ou=ug,dc=unil,dc=ch",
            "(unilMemberOf={$ldapAuthGroup})",
            ["unilMemberOf"],
            1
        );

        $entries = ldap_get_entries($ldapConn, $searchResult);
        $result = !empty($entries[0]);

        self::disconnect($ldapConn);
        return $result;
    }

    private static function disconnect($ldapConn) {
        if (is_resource($ldapConn)) {
            ldap_close($ldapConn);
        }
    }
}
