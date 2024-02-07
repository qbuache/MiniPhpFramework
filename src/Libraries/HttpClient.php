<?php

namespace App\Libraries;

use App\Consts\HttpMethod;
use App\Exceptions\SystemException;
use App\Helpers\Logger;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

class HttpClient {

    private CookieJar $cookieJar;
    private bool $debug = false;
    private GuzzleClient $guzzle;
    private bool $httpErrors = false;
    private bool $log = false;
    private array $logAppendData = [];
    private string $logFile = "info";
    private string $logLevel = "info";
    private ?string $logPattern = null;
    private bool $logQueryOptions = false;
    private string $logType = "real";
    private array $globalOptions = [];
    private array $possibleLogTypes = ["real", "router"];
    private ?HandlerStack $stack = null;
    private array $transactions = [];

    public function delete(string $url, array $options = []) {
        return $this->request(HttpMethod::DELETE, $url, $options);
    }

    public function get(string $url, array $options = []) {
        return $this->request(HttpMethod::GET, $url, $options);
    }

    public function getCookieJar(): CookieJar {
        return $this->cookieJar;
    }

    public function getDebug(): bool {
        return $this->debug;
    }

    public function getGuzzle(): GuzzleClient {
        return $this->guzzle;
    }

    public function getLog(): bool {
        return $this->log;
    }

    public function getLogType(): string {
        return $this->logType;
    }

    public function getGlobalOptions(): array {
        return $this->globalOptions;
    }

    public function getTransactions(): array {
        return $this->transactions;
    }

    public function init() {
        $this->guzzle = new GuzzleClient([
            "handler" => $this->stack,
            "http_errors" => $this->httpErrors,
        ]);
    }

    public function post(string $url, array $options = []) {
        return $this->request(HttpMethod::POST, $url, $options);
    }

    public function patch(string $url, array $options = []) {
        return $this->request(HttpMethod::PATCH, $url, $options);
    }

    public function setLogAppendData(array $data): HttpClient {
        $this->logAppendData = $data;
        return $this;
    }

    public function setLogFile(string $file): HttpClient {
        $this->logFile = $file;
        return $this;
    }

    public function setLogLevel(string $level): HttpClient {
        $this->logLevel = $level;
        return $this;
    }

    public function setLogQueryOptions(bool $options = true): HttpClient {
        $this->logQueryOptions = $options;
        return $this;
    }

    public function setLogPattern(string $pattern): HttpClient {
        $this->logPattern = $pattern;
        return $this;
    }

    public function setLogType(string $type): HttpClient {
        $this->logType = $type;
        if (!in_array($this->logType, $this->possibleLogTypes)) {
            throw new SystemException("Not implemented log type '{$this->logType}'", 500);
        }
        return $this;
    }

    public function setGlobalOptions(array $options): HttpClient {
        $this->globalOptions = $options;
        return $this;
    }

    public function setHttpErrors(bool $httpErrors = true): HttpClient {
        $this->httpErrors = $httpErrors;
        return $this;
    }

    public function useCookieJar(): HttpClient {
        $this->cookieJar = new CookieJar();
        return $this;
    }

    public function useDebug($debug = true): HttpClient {
        $this->debug = $debug;
        return $this;
    }

    public function useLog($log = true): HttpClient {
        $this->log = $log;
        return $this;
    }

    public function useTransactions(): HttpClient {
        $history = Middleware::history($this->transactions);
        $this->stack = HandlerStack::create();
        $this->stack->push($history);
        return $this;
    }

    public function request(string $method, string $url, array $options = []) {
        if ($this->getCookieJar()) {
            $options = array_merge($options, ["cookies" => $this->getCookieJar()]);
        }

        if ($this->getGlobalOptions()) {
            $options = array_merge($options, $this->getGlobalOptions());
        }

        $this->writeDebug($method, $url);
        $this->writeLog($method, $url, $options);

        return $this->guzzle->request($method, $url, $options);
    }

    private function writeLog(string $method, string $url, array $options) {
        if ($this->log) {
            if ($this->logType === "router") {
                $method = request()->getRoute()->getMethod();
                $url = request()->getRoute()->getComputed();
            }

            $data = [$method, $url, ...$this->logAppendData];
            $pattern = $this->logPattern ? $this->logPattern : trim(str_repeat("%s ", count($data)));

            Logger::log(
                $this->logFile,
                $this->logLevel,
                sprintf($pattern, ...$data),
                $this->logQueryOptions ? $options : null
            );
        }
    }

    private function writeDebug($method, $url) {
        if ($this->debug) {
            dump("{$method} : {$url}");
        }
    }
}
