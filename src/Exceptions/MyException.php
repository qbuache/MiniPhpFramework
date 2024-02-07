<?php

namespace App\Exceptions;

use Exception;

/**
 * Extension de l'exception par défaut afin d'ajouter
 * plus d'informations pouvant être renvoyées lors d'un message HTTP
 */
abstract class MyException extends Exception {

    /**
     * Détails supplémentaires sur l'exception
     *
     * @var array
     */
    private array $details = [];

    /**
     * @param string            $message    Message de l'exception
     * @param integer           $code       Code d'erreur ou code HTTP de l'exception
     * @param Exception|null    $previous   L'exception précédemment lancée
     */
    public function __construct(string $message, int $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retourne les détails de l'exception
     *
     * @return array
     */
    public function getDetails() {
        return $this->details;
    }

    /**
     * Ajoute des détails à l'exception
     *
     * @param array $details
     * @return this
     */
    public function setDetails(array $details) {
        $this->details = $details;
        return $this;
    }
}
