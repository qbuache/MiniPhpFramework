<?php

namespace App\Core;

use App\Consts\HttpMethod;
use App\Helpers\Utils;
use App\Core\Authenticate;
use App\Exceptions\ApiException;

class Router {

    public static function route() {
        $method = request()->method();
        $target = request()->target();

        // Permet les requêtes CORS
        response()->addHeader(["Access-Control-Allow-Origin: *", "Access-Control-Allow-Headers: *"]);
        if ($method === HttpMethod::OPTIONS && request()->header("Access-Control-Request-Method") !== null) {
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
}
