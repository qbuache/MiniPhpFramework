<?php

namespace App\Core;

use App\Consts\HttpMethod;
use App\Helpers\Utils;
use App\Core\Authenticate;
use App\Exceptions\ApiException;

class Router {

    public static function route() {
        $method = self::getMethod();
        $target = self::getTarget();

        /**
         * Permet les requêtes CORS
         */
        if ($method === HttpMethod::OPTIONS) {
            return true;
        }

        if (empty($target) || $target == "/") {
            Utils::redirect(config("router.default"));
        }

        foreach (config("router.routes") as $routesFile) {
            foreach ((new $routesFile)->routes() as $route) {
                if ($route->isCurrentRoute($method, $target)) {
                    if (Authenticate::with($route->getAuth())) {
                        return $route->execute();
                    } else {
                        throw new ApiException("Forbidden", 403);
                    }
                }
            }
        }

        Utils::redirect(config("router.404"));
    }

    public static function getMethod() {
        return strtoupper($_POST["_method"] ?? $_SERVER["REQUEST_METHOD"]);
    }

    public static function getTarget() {
        return str_replace(__ROOT__, "", strtok($_SERVER["REQUEST_URI"], "?"));
    }
}
