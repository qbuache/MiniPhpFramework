<?php

namespace App\Routes;

use App\Controllers\AppController;
use App\Core\Route;

class App {

    public function routes() {
        return (new Route)->controller(AppController::class)->prefix("/app")->group(
            [
                (new Route)->get("/missing-group", "missingGroup"),
                (new Route)->any("/page-404", "page404"),
            ],
        );
    }
}
