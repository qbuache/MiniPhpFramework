<?php

namespace App\Core;

abstract class Singleton {

    private static $instances = [];

    public static function getInstance() {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }
        return self::$instances[$cls];
    }
}
