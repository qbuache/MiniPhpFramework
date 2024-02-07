<?php

namespace App;

use App\Core\Router;
use Dotenv\Dotenv;
use Exception;

class App {

    function __construct() {
        $this->bootstrap();
    }

    public function execute() {
        try {
            $response = Router::route();
        } catch (Exception $th) {
            $response = $th;
        }
        echo response()->setData($response);
    }

    private function bootstrap() {
        define("__APP__", __DIR__);
        define("__ROOT__", str_replace("/index.php", "", $_SERVER["SCRIPT_NAME"]));
        Dotenv::createImmutable(__DIR__ . '/../')->load();
    }
}
