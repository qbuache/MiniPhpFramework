<?php

namespace App\Helpers;

use App\Exceptions\MyException;
use DateTimeImmutable;

class Logger {

    public static function error(string $message, array $data = null) {
        return self::logWithUniqueId("error", "error", $message, $data);
    }

    public static function exception(MyException $exception) {
        $previousException = $exception->getPrevious();
        $message = (empty($previousException) ? $exception : $previousException)->getMessage();
        $trace = (empty($previousException) ? $exception : $previousException)->getTraceAsString();
        return self::error("{$message}\n{$trace}", $exception->getDetails());
    }

    public static function info(string $message, array $data = null) {
        return self::logInFile("info", "info", $message, $data);
    }

    public static function log(string $file, string $level, string $message, array $data = null) {
        return self::logInFile($file, $level, $message, $data);
    }

    public static function warn(string $message, array $data = null) {
        return self::logWithUniqueId("warn", "warn", $message, $data);
    }

    private static function logInFile(string $file, string $level, string $message, array $data = null) {
        $date = (new DateTimeImmutable("now"))->format("Y-m-dTH:i:s");
        $encodedData = empty($data) ? "" : json_encode($data);

        Storage::writeFile(
            self::path(Utils::slugify($file, "_") . ".log"),
            sprintf("[%s] %s %s %s\n", $date, strtoupper($level), $message, $encodedData)
        );
    }

    /**
     * @return string L'unique ID de l'entrée de log
     */
    private static function logWithUniqueId(string $file, string $level, string $message, array $data = null) {
        $uniqueId = uniqid();
        self::logInFile($file, $level, "{$uniqueId} {$message}", $data);
        return $uniqueId;
    }

    private static function path(string $path = "") {
        return "log/{$path}";
    }
}
