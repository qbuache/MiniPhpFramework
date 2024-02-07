<?php

namespace App\Helpers;

class Cache {

    /**
     * Mettre en cache pour une durée déterminée
     *
     * @param string $name  Le nom du cache
     * @param integer $duration  Durée en minutes du cache
     * @param function $callback La fonction générant les données si pas de cache
     * @return mixed  Les données mises en cache
     */
    public static function remember(string $name, int $duration, $callback) {
        $hasCache = false;
        $existingCache = self::get($name);
        if ($existingCache !== false) {
            if ($existingCache->expired === false) {
                $hasCache = true;
                $data = self::read($existingCache);
            } else {
                self::remove($existingCache);
            }
        }

        if ($hasCache === false) {
            $data = $callback();
            self::store($name, $duration, $data);
        }
        return $data;
    }

    /**
     * Mettre en cache de façon permanente
     *
     * @param string $name  Le nom du cache
     * @param function $callback La fonction générant les données si pas de cache
     * @return mixed  Les données mises en cache
     */
    public static function rememberForever(string $name, $callback) {
        return self::remember($name, 9999999, $callback);
    }

    /**
     * Supprime un cache nominativement
     *
     * @param string $name  Le nom du cache
     * @return bool  Le résultat de la suppression
     */
    public static function forget(string $name) {
        $existingCache = self::get($name);
        return $existingCache !== false && self::remove($existingCache);
    }

    /**
     * Supprime tous les caches
     *
     * @return integer  Le nombre de caches supprimés
     */
    public static function forgetAll() {
        $files = self::getFiles("*");
        $countDeleted = 0;
        foreach ($files as $file) {
            if (self::remove(self::getMetadata($file))) {
                $countDeleted++;
            }
        }
        return $countDeleted;
    }

    private static function get(string $name) {
        $slug = Utils::slugify($name);
        $files = self::getFiles($slug);
        if (!empty($files[0])) {
            return self::getMetadata($files[0]);
        }
        return false;
    }

    private static function getFiles($name) {
        return Storage::getGlob(self::path("{$name}_*"));
    }

    private static function getMetadata($file) {
        $basename = basename($file);
        $metadata = explode("_", $basename);
        return (object)[
            "file" => $file,
            "basename" => $basename,
            "name" => $metadata[0],
            "expiry" => $metadata[1],
            "expired" => time() > $metadata[1],
        ];
    }

    private static function path(string $path = "") {
        return "cache/{$path}";
    }

    private static function read(object $cache) {
        return json_decode(Storage::readFile(self::path($cache->basename)), true);
    }

    private static function remove(object $cache) {
        return Storage::removeFile(self::path($cache->basename));
    }

    private static function store($name, $duration, $data) {
        $expiry = strtotime("+$duration minutes", time());
        return Storage::createFile(self::path("{$name}_{$expiry}"), json_encode($data));
    }
}
