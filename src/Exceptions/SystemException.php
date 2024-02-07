<?php

namespace App\Exceptions;

use App\Helpers\Logger;
use Exception;

/**
 * Exception spécifique à des problèmes systèmes qui 
 * doivent être loggés mais pas transmis au client
 */
class SystemException extends MyException {

    /**
     * Id du log
     */
    private string $uniqueId;

    /**
     * @param string            $message    Message de l'exception
     * @param integer           $code       Code d'erreur ou code HTTP de l'exception
     * @param Exception|null    $previous   L'exception précédemment lancée
     */
    public function __construct(string $message, int $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->uniqueId = Logger::exception($this);
    }

    /**
     * Retourne l'ID unique de l'entrée de log
     *
     * @return array
     */
    public function getUniqueId() {
        return $this->uniqueId;
    }
}
