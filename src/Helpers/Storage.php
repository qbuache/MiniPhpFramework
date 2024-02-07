<?php

namespace App\Helpers;

class Storage {

    public static function createDirectory($path, $permissions = 0777, $recursive = true) {
        if (!self::exists($path)) {
            return mkdir(self::path($path), $permissions, $recursive);
        }
        return true;
    }

    public static function createFile($path, $data) {
        return file_put_contents(self::path($path), $data) !== false;
    }

    public static function exists($path) {
        return file_exists(self::path($path));
    }

    public static function isDirectory($path) {
        return is_dir(self::path($path));
    }

    public static function isFile($path) {
        return is_file(self::path($path));
    }

    public static function getFiles($path) {
        return scandir(self::path($path));
    }

    public static function getGlob($path) {
        return glob(self::path($path));
    }

    public static function path($path = "") {
        return __APP__ . "/../storage/{$path}";
    }

    public static function readFile($path, $default = false) {
        return self::exists($path) ? file_get_contents(self::path($path)) : $default;
    }

    public static function removeDirectory($path) {
        return rmdir(self::path($path));
    }

    public static function removeFile($path) {
        return unlink(self::path($path));
    }

    public static function writeFile(string $path, $data, int $flags = FILE_APPEND) {
        return file_put_contents(self::path($path), $data, $flags);
    }
}
