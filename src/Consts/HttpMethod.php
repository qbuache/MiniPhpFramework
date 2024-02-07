<?php

namespace App\Consts;

use ReflectionClass;

class HttpMethod {

    public const ANY = "ANY";
    public const GET = "GET";
    public const OPTIONS = "OPTIONS";
    public const POST = "POST";
    public const PATCH = "PATCH";
    public const DELETE = "DELETE";

    static function allowed() {
        $oClass = new ReflectionClass(get_called_class());
        return array_filter($oClass->getConstants(), fn ($element) => is_string($element));
    }
}
