<?php

namespace App\Exceptions;

use App\Helpers\Logger;

/**
 * Exception spécifique à des problèmes systèmes qui 
 * doivent être loggés mais pas transmis au client
 */
class SystemException extends MyException {

    /**
     * Id du log
     */
    private ?string $uniqueId = null;

    /**
     * Retourne l'ID unique de l'entrée de log
     *
     * @return ?string
     */
    public function getUniqueId(): ?string {
        return $this->uniqueId;
    }

    /**
     * Log l'exception
     *
     * @return SystemException
     */
    public function log(): SystemException {
        $this->uniqueId = Logger::exception($this);
        return $this;
    }
}
