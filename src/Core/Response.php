<?php

namespace App\Core;

use App\Exceptions\ApiException;
use App\Exceptions\MyException;
use App\Exceptions\SystemException;
use App\Helpers\Utils;
use Exception;
use Psr\Http\Message\ResponseInterface;

/**
 * Permet de définir la valeur de retour de l'application
 */
class Response extends Singleton {

    /**
     * Le format de retour
     * 
     * @var string
     */
    private string $as = "html";

    /**
     * Le code HTTP de retour
     * 
     * @var int
     */
    private int $code = 200;

    /**
     * Les données de la réponse
     * 
     * @var mixed
     */
    private $data;

    /**
     * Les headers à ajouter à la réponse
     * 
     * @var mixed
     */
    private $headers = [];

    protected function __construct() {
    }

    /**
     * Serialize la réponse selon le type demandé
     * 
     * @return string
     */
    public function __toString() {
        $response = $this->getResponse();
        $this->withCode();
        $this->withHeaders();
        return $response;
    }

    /**
     * Ajoute un ou des headers à la réponse
     * 
     * @param string|array $header
     * @param boolean $replace
     * @return Response
     */
    public function addHeader($headers, bool $replace = true): Response {
        $headers = is_array($headers) ? $headers : [$headers];
        foreach ($headers as $header) {
            $this->headers[$header] = $replace;
        }
        return $this;
    }

    /**
     * Définit que la réponse sera en Csv
     * 
     * @return Response
     */
    public function asCsv(): Response {
        return $this->as("csv");
    }

    /**
     * Définit que la réponse sera en Html
     * 
     * @return Response
     */
    public function asHtml(): Response {
        return $this->as("html");
    }

    /**
     * Définit que la réponse sera en Json
     * 
     * @return Response
     */
    public function asJson(): Response {
        return $this->as("json");
    }

    /**
     * Définit que la réponse sera en Yaml
     * 
     * @return Response
     */
    public function asYaml(): Response {
        return $this->as("yaml");
    }

    /**
     * Définit le code de retour HTTP
     * 
     * @param integer $code
     * @return Response
     */
    public function setCode(int $code) {
        $this->code = $code;
        return $this;
    }

    /**
     * Définit la réponse de l'application
     *
     * @param Exception|mixed $data
     * @return Response
     */
    public function setData($data): Response {
        $response = $data;
        if (is_a($data, ResponseInterface::class)) {
            $this->setCode($data->getStatusCode());
            $response = $this->fromResponseInterface($data);
        } else if (is_a($data, Exception::class)) {
            $this->setCode($data->getCode())->asJson();

            if (!is_a($data, MyException::class)) {
                $data = Utils::exceptionToSystemException($data);
            }

            if (is_a($data, ApiException::class)) {
                $response = $this->fromApiException($data);
            } else if (is_a($data, SystemException::class)) {
                $response = $this->fromSystemException($data);
            }
        }
        $this->data = $response;
        return $this;
    }

    /**
     * Renvoie la réponse en tant que CSV
     * 
     * @return string
     */
    public function toCsv(): string {
        $this->addHeader("Content-Type: text/csv");
        return $this->data;
    }

    /**
     * Renvoie la réponse en tant qu'HTML
     * 
     * @return string
     */
    public function toHtml(): string {
        $this->addHeader("Content-Type: text/html");
        return $this->data;
    }

    /**
     * Renvoie la réponse en tant que JSON
     * 
     * @return string
     */
    public function toJson(): string {
        $this->addHeader("Content-Type: application/json");
        return json_encode($this->data);
    }

    /**
     * Renvoie la réponse en tant que YAML
     * 
     * @return string
     */
    public function toYaml(): string {
        $this->addHeader("Content-Type: application/x-yaml");
        return $this->data;
    }

    /**
     * Définit le type de réponse
     * 
     * @param string $as
     * @return Response
     */
    private function as(string $as): Response {
        $this->as = $as;
        return $this;
    }

    /**
     * Construit la réponse à partir d'une ApiException
     * 
     * @param ApiException $data
     * @return array
     */
    private function fromApiException(ApiException $data): array {
        return [
            "code" => $data->getCode(),
            "error" => $data->getMessage(),
            "details" => $data->getDetails(),
        ];
    }

    /**
     * Construit la réponse à partir d'une ResponseInterface
     * 
     * @param ResponseInterface $data
     * @return array
     */
    private function fromResponseInterface(ResponseInterface $data): array {
        $response = [
            "code" => $data->getStatusCode(),
            "message" => $data->getReasonPhrase(),
        ];
        if (!empty($data->group_id)) {
            $response["group_id"] = $data->group_id;
        }
        return $response;
    }

    /**
     * Construit la réponse à partir d'une SystemException
     * 
     * @param SystemException $data
     * @return array
     */
    private function fromSystemException(SystemException $data): array {
        return [
            "code" => empty($data->getPrevious()) ? $data->getCode() : 500,
            "error" => "Internal server error",
            "unique_id" => $data->getUniqueId(),
        ];
    }

    /**
     * Retourne la réponse dans le format demandé
     * 
     * @return mixed
     */
    private function getResponse() {
        switch ($this->as) {
            case "csv":
                return $this->toCsv();
            case "html":
                return $this->toHtml();
            case "json":
                return $this->toJson();
            case "yaml":
                return $this->toYaml();
            default:
                throw new SystemException("Not implemented return type", 500);
        }
    }

    /**
     * Ajoute le code de retour HTTP à la réponse
     * 
     * @return void
     */
    private function withCode() {
        http_response_code($this->code);
    }

    /**
     * Ajoute les Headers à la réponse
     * 
     * @return void
     */
    private function withHeaders() {
        foreach ($this->headers as $header => $replace) {
            header($header, $replace);
        }
    }
}
