<?php

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

if (!function_exists("config")) {
    function config($key = null, $default = null) {
        return Config::getInstance()::get($key, $default);
    }
}

if (!function_exists("response")) {
    /**
     * Retourne l'objet Response en cours permettant de définir la valeur de retour de l'application
     */
    function response(): Response {
        return Response::getInstance();
    }
}

if (!function_exists("request")) {
    function request(): Request {
        return Request::getInstance();
    }
}

if (!function_exists("session")) {
    function session(): Session {
        return Session::getInstance();
    }
}
