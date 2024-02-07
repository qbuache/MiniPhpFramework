<?php

namespace App\Core;

class Config extends Singleton {

    private static $configs = [];

    protected function __construct() {
        $configs = [];
        foreach (glob(__APP__ . "/Configs/*.php") as $filename) {
            $configs[basename($filename, ".php")] = include $filename;
        }
        self::$configs = $configs;
    }

    public static function get($key = null, $default = null) {
        $result = self::$configs;
        if (isset($key)) {
            $parts = explode(".", $key);
            foreach ($parts as $part) {
                $result = isset($result[$part]) ? $result[$part] : $default;
            }
        }
        return $result;
    }
}
