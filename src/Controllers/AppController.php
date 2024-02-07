<?php

namespace App\Controllers;

use App\Exceptions\ApiException;
use App\Helpers\Utils;

class AppController {

    public function missingGroup() {
        response()->setCode(404);
        return Utils::view("app/missingGroup");
    }

    public function page404() {
        response()->setCode(404);
        if (request()->accept("application/json")) {
            throw new ApiException("Not Found", 404);
        } else {
            return Utils::view("app/page404");
        }
    }
}
