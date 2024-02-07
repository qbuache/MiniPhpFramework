<?php

namespace App\Helpers;

use App\Exceptions\SystemException;
use DOMDocument;
use DOMXPath;
use Exception;

class Utils {

    /**
     * Transforme une Exception de base en SystemException
     *
     * @param Exception $exception
     * @return SystemException
     */
    public static function exceptionToSystemException(Exception $exception): SystemException {
        return new SystemException($exception->getMessage(), $exception->getCode(), $exception);
    }

    public static function extractXPath($html) {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_use_internal_errors(false);
        return new DOMXPath($dom);
    }

    public static function redirect($to, $replace = true, $code = 301) {
        $prepend = strpos($to, "://") !== false ? "" : __ROOT__;
        exit(header("Location: " . "{$prepend}{$to}", $replace, $code));
    }

    public static function slugify($text, $separator = "-") {
        return preg_replace("/[^a-z0-9-]+/", $separator, strtolower(trim($text)));
    }

    /**
     * Emballe un texte dans des éléments s'ils ne sont pas présents
     */
    public static function wrapIn(string $text, string $before, string $after): string {
        if ($text[0] !== $before) {
            $text = "{$before}{$text}";
        }

        if ($text[strlen($text) - 1] !== $after) {
            $text = "{$text}{$after}";
        }

        return $text;
    }

    public static function view(string $view, array $data = []) {
        ob_start();
        extract(array_merge($data, [
            "view" => $view,
            "url" => fn ($route) => __ROOT__ . $route,
        ]));
        include(__APP__ . "/Views/index.php");
        return ob_get_clean();
    }
}
