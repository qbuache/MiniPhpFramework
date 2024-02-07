<?php

namespace App\Routes;

use App\Controllers\AppController;
use App\Core\Route;

class App {

    public function routes() {
        return [
            (new Route)->get("/missing-group", "missingGroup")->controller(AppController::class),
            (new Route)->any("/page-404", "page404")->controller(AppController::class),
        ];
    }
}
