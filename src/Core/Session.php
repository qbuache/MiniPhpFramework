<?php

namespace App\Core;

class Session extends Singleton {

    /**
     * Détruit la session en cours
     */
    public static function destroy() {
        return session_destroy();
    }

    /**
     * Récupére une valeur dans la session, ou la valeur par défaut
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key = null, $default = null) {
        return isset($key) ? ($_SESSION[$key] ?? $default) : $_SESSION;
    }

    /**
     * Récupére une valeur "flash" dans la session et supprime l'entrée, ou la valeur par défaut
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getFlash(string $key, $default = null) {
        $value = self::get("flash_{$key}", $default);
        self::unset("flash_{$key}");
        return $value;
    }

    /**
     * Démarre une nouvelle sesion
     */
    public static function start() {
        return session_start();
    }

    /**
     * Stocke une valeur dans la session
     *
     * @param string $key
     * @param mixed $value
     * @return mixed  Retourne la valeur stockée
     */
    public static function store(string $key, $value) {
        return $_SESSION[$key] = $value;
    }

    /**
     * Stocke une valeur "flash" dans la session
     *
     * @param string $key
     * @param mixed $value
     * @return mixed  Retourne la valeur stockée
     */
    public static function storeFlash(string $key, $value) {
        return self::store("flash_{$key}", $value);
    }

    /**
     * Supprime toutes les valeurs stockées
     */
    public static function unset() {
        return session_unset();
    }

    /**
     * Supprime la valeur dans la session
     */
    public static function remove($key) {
        unset($_SESSION[$key]);
    }
}
