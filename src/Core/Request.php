<?php

namespace App\Core;

use App\Consts\HttpMethod;
use App\Exceptions\ApiException;

/**
 * Permet de récupérer des informations sur la requête en cours
 */
class Request extends Singleton {

    /**
     * La route en cours d'utilisation
     * 
     * @var Route
     */
    private static Route $route;

    /**
     * Retourne si la requête accepte le type/mime demandé
     * 
     * @param string $accept
     * @return bool
     */
    public static function accept(string $accept): bool {
        return in_array(strtolower($accept), self::accepts());
    }

    /**
     * Retourne la liste des type/mimes acceptés
     * 
     * @return array
     */
    public static function accepts(): array {
        return array_map(
            fn ($mime) => strtolower(explode(";", $mime)[0]),
            explode(",", self::header("Accept", "*/*"))
        );
    }

    /**
     * Retourne la valeur de la clé dans tous les tableaux, ou le tableau si aucune clé n'est renseignée
     * 
     * @param ?string $key
     * @param mixed $default
     * @return mixed
     */
    public static function all(?string $key = null, $default = null) {
        $data = array_merge(self::get(), self::post(), self::patch(), self::delete(), self::files());
        return self::getDataOrDefault($data, $key, $default);
    }

    /**
     * Retourne la valeur de la clé dans php://input, ou le tableau si aucune clé n'est renseignée
     * 
     * @param ?string $key
     * @param mixed $default
     * @return mixed
     */
    public static function delete(?string $key = null, $default = null) {
        return self::getDataOrDefault(self::input(), $key, $default);
    }

    /**
     * Retourne la valeur de la clé dans $_ENV, ou le tableau si aucune clé n'est renseignée
     * 
     * @param ?string $key
     * @param mixed $default
     * @return mixed
     */
    public static function env(?string $key = null, $default = null) {
        return self::getDataOrDefault($_ENV, $key, $default);
    }

    /**
     * Retourne la valeur de la clé dans $_FILES, ou le tableau si aucune clé n'est renseignée
     * 
     * @param ?string $key
     * @param mixed $default
     * @return mixed
     */
    public static function files(?string $key = null, $default = null) {
        return self::getDataOrDefault($_FILES, $key, $default);
    }


    /**
     * Retourne la valeur de la clé dans $_GET, ou le tableau si aucune clé n'est renseignée
     * 
     * @param ?string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(?string $key = null, $default = null) {
        return self::getDataOrDefault($_GET, $key, $default);
    }

    /**
     * Retourne la route en cours d'utilisation
     * 
     * @return Route
     */
    public static function getRoute() {
        return self::$route;
    }

    /**
     * Retourne le header demandé ou tous les headers si aucune clé n'est renseignée
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function header(string $key, $default = null) {
        return getallheaders()[$key] ?? $default;
    }

    /**
     * Retourne tous les headers de la reqûete
     * 
     * @return array
     */
    public static function headers(): array {
        return getallheaders();
    }

    /**
     * Retourne le contenu du buffer PHP
     * 
     * @param boolean $parsed
     * @return array|string|bool
     */
    public static function input(bool $parsed = true) {
        $input = file_get_contents("php://input");
        if ($parsed) {
            parse_str($input, $parsed);
            return $parsed;
        }
        return $input;
    }

    /**
     * Retourne la méthode HTTP utilisée
     * 
     * @return string
     */
    public static function method() {
        return strtoupper($_POST["_method"] ?? $_SERVER["REQUEST_METHOD"]);
    }

    /**
     * Retourne les données reçues en fonction de la méthode de la route en cours
     * 
     * @return mixed
     */
    public static function methodData() {
        switch (request()->method()) {
            case HttpMethod::GET:
                return self::get();
            case HttpMethod::POST:
                return self::post();
            case HttpMethod::PATCH:
                return self::patch();
            case HttpMethod::DELETE:
                return self::delete();
            default:
                throw new ApiException("Not implemented HTTP Method", 400);
        }
    }

    /**
     * Retourne la valeur de la clé dans php://input, ou le tableau si aucune clé n'est renseignée
     * 
     * @param ?string $key
     * @param mixed $default
     * @return mixed
     */
    public static function patch(?string $key = null, $default = null) {
        return self::getDataOrDefault(self::input(), $key, $default);
    }

    /**
     * Retourne la valeur de la clé dans $_POST, ou le tableau si aucune clé n'est renseignée
     * 
     * @param ?string $key
     * @param mixed $default
     * @return mixed
     */
    public static function post(?string $key = null, $default = null) {
        return self::getDataOrDefault($_POST, $key, $default);
    }

    /**
     * Retourne la valeur de la clé dans $_SERVER, ou le tableau si aucune clé n'est renseignée
     * 
     * @param ?string $key
     * @param mixed $default
     * @return mixed
     */
    public static function server(?string $key = null, $default = null) {
        return self::getDataOrDefault($_SERVER, $key, $default);
    }

    /**
     * Définit la route en cours d'utilisation
     * 
     * @param Route $route
     * @return void
     */
    public static function setRoute(Route $route) {
        self::$route = $route;
    }

    /**
     * Retourne la chemin demandé
     * 
     * @return string
     */
    public static function target() {
        return str_replace(__ROOT__, "", strtok($_SERVER["REQUEST_URI"], "?"));
    }

    /**
     * Retourne la valeur de la clé dans le tableau, ou le tableau si aucune clé n'est renseignée
     * 
     * @param array $data
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private static function getDataOrDefault(array $data, ?string $key, $default) {
        return isset($key) ? ($data[$key] ?? $default) : $data;
    }
}
