<?php

namespace App\Core;

use App\Consts\HttpMethod;
use App\Exceptions\SystemException;
use App\Helpers\Utils;

/**
 * Permet de créer une Route liant un chemin à une fonction de controller
 */
class Route {

    /**
     * L'authentification à utiliser
     *
     * @var string|null
     */
    private ?string $auth = null;

    /**
     * Le chemin définitif calculé
     *
     * @var string
     */
    private string $computed = "";

    /**
     * Le controller à utiliser
     *
     * @var string
     */
    private string $controller = "";

    /**
     * La fonction de controller à utiliser
     *
     * @var string
     */
    private string $function = "";

    /**
     * Si la route est la route sélectionée
     *
     * @var boolean
     */
    private bool $isCurrentRoute = false;

    /**
     * La méthode HTTP utilisée
     *
     * @var string
     */
    private string $method = "";

    /**
     * Les paramètres à passer au controller
     *
     * @var array
     */
    private array $parameters = [];

    /**
     * Le préfixe à ajouter avant le chemin
     *
     * @var string
     */
    private string $prefix = "";

    /**
     * Les captures groups utilisés pour extraires les paramètres du chemin
     *
     * @var array
     */
    private array $regexs = [];

    /**
     * Le chemin de la route
     *
     * @var string
     */
    private string $target = "";

    /**
     * Si la route utilise des regexs
     *
     * @var boolean
     */
    private bool $useRegex = false;

    /**
     * Créé une route utilisant la méthode HTTP ANY
     *
     * @param string $target    Le chemin relatif auquel répondra cette route
     * @param string $function  La fonction à appeler dans le controller
     * @return Route
     */
    public function any(string $target, string $function): Route {
        return $this->using(HttpMethod::ANY, $target, $function);
    }

    /**
     * Définit le type d'authentification à utiliser
     *
     * @param string $auth
     * @return Route
     */
    public function auth(string $auth): Route {
        $this->auth = strtolower($auth);
        return $this;
    }

    /**
     * Définit le controller à utiliser (Controller::class)
     *
     * @param string $controller
     * @return Route
     */
    public function controller(string $controller): Route {
        $this->controller = $controller;
        return $this;
    }

    /**
     * Créé une route utilisant la méthode HTTP DELETE
     *
     * @param string $target    Le chemin relatif auquel répondra cette route
     * @param string $function  La fonction à appeler dans le controller
     * @return Route
     */
    public function delete(string $target, string $function): Route {
        return $this->using(HttpMethod::DELETE, $target, $function);
    }

    /**
     * Exécute la fonction du controller avec les paramètres donnés
     *
     * @return mixed
     */
    public function execute() {
        return (new $this->controller)->{$this->function}(...$this->parameters);
    }

    /**
     * Créé une route utilisant la méthode HTTP GET
     *
     * @param string $target    Le chemin relatif auquel répondra cette route
     * @param string $function  La fonction à appeler dans le controller
     * @return Route
     */
    public function get(string $target, string $function): Route {
        return $this->using(HttpMethod::GET, $target, $function);
    }

    /**
     * Retourne l'authentification utlisée
     *
     * @return ?string
     */
    public function getAuth(): ?string {
        return $this->auth;
    }

    /**
     * Retourne le chemin définitif calculé lors de la sélection de la route
     *
     * @return string
     */
    public function getComputed(): string {
        return $this->computed;
    }

    /**
     * Retourne le controller utilisé
     *
     * @return string
     */
    public function getController(): string {
        return $this->controller;
    }

    /**
     * Retourne la fonction à utiliser dans le controller
     *
     * @return string
     */
    public function getFunction(): string {
        return $this->function;
    }

    /**
     * Retourne si la route est la route en cours d'utilisation
     *
     * @return boolean
     */
    public function getIsCurrentRoute(): bool {
        return $this->isCurrentRoute;
    }

    /**
     * Retourne le chemin complet en y ajoutant le préfixe
     *
     * @return string
     */
    public function getPath(): string {
        return "{$this->prefix}{$this->target}";
    }

    /**
     * Retourne les paramètres
     *
     * @return array
     */
    public function getParameters(): array {
        return $this->parameters;
    }

    /**
     * Retourne le préfixe
     *
     * @return string
     */
    public function getPrefix(): string {
        return $this->prefix;
    }

    /**
     * Retourne le regex qui sera utilisé afin de sélectionner la route
     *
     * @return string
     */
    public function getRegex(): string {
        $keys = array_keys($this->regexs);
        $regexs = array_values($this->regexs);
        return "/^" . str_replace(
            array_merge($keys, ["/"]),
            array_merge($regexs, ["\/"]),
            $this->getPath()
        ) . "$/";
    }

    /**
     * Retourne les captures groups utilisés pour extraires les paramètres du chemin
     *
     * @return array
     */
    public function getRegexs(): array {
        return $this->regexs;
    }

    /**
     * Retourne le chemin sans le préfixe
     *
     * @return string
     */
    public function getTarget(): string {
        return $this->target;
    }

    /**
     * Retourne la méthode HTTP utilisée
     *
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }

    /**
     * Transmet les paramètres définis sur la route aux sous-routes
     *
     * @param array ...$groups
     * @return array
     */
    public function group(array ...$groups): array {
        foreach ($groups as $routes) {
            foreach ($routes as $route) {
                if ($this->auth) {
                    $route->auth($this->auth);
                }
                if ($this->controller) {
                    $route->controller($this->controller);
                }
                if ($this->regexs) {
                    foreach ($this->regexs as $key => $value) {
                        $route->where($key, $value);
                    }
                }
                if ($this->prefix) {
                    $route->prefix($this->prefix);
                }
            }
        }
        return array_merge(...$groups);
    }

    /**
     * Teste si la route correspond à la demande, et la sélectionne
     *
     * @param string $method
     * @param string $target
     * @return boolean
     */
    public function isCurrentRoute(string $method, string $target): bool {
        if ($this->method == $method || $this->method == HttpMethod::ANY) {
            if (!$this->useRegex) {
                if ($this->getPath() == $target) {
                    $this->computed = $target;
                }
            } else {
                preg_match($this->getRegex(), $target, $matches);
                if (count($matches) > 1) {
                    $this->computed = $matches[0];
                    $this->parameters = array_slice($matches, 1);
                }
            }
        }
        if ($this->isCurrentRoute = !empty($this->computed)) {
            request()->setRoute($this);
        }
        return $this->isCurrentRoute;
    }

    /**
     * Créé une route utilisant la méthode HTTP PATCH
     *
     * @param string $target    Le chemin relatif auquel répondra cette route
     * @param string $function  La fonction à appeler dans le controller
     * @return Route
     */
    public function patch(string $target, string $function): Route {
        return $this->using(HttpMethod::PATCH, $target, $function);
    }

    /**
     * Créé une route utilisant la méthode HTTP POST
     *
     * @param string $target    Le chemin relatif auquel répondra cette route
     * @param string $function  La fonction à appeler dans le controller
     * @return Route
     */
    public function post(string $target, string $function): Route {
        return $this->using(HttpMethod::POST, $target, $function);
    }

    /**
     * Définit un préfixe qui sera ajouté avant le chemin
     *
     * @param string $prefix
     * @return Route
     */
    public function prefix(string $prefix): Route {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Définit un regex afin d'extraire un paramètre du chemin
     *
     * @param string $key
     * @param string $regex
     * @return Route
     */
    public function where(string $key, string $regex): Route {
        $wrappedKey = Utils::wrapIn($key, "{", "}");
        $this->regexs[$wrappedKey] = Utils::wrapIn($regex, "(", ")");
        $this->useRegex = true;
        return $this;
    }

    /**
     * Définit un regex numérique ([[:digit:]]+) afin d'extraire un paramètre du chemin
     *
     * @param string $key
     * @param string $regex
     * @return Route
     */
    public function whereInt(string $key): Route {
        $this->where($key, "[[:digit:]]+");
        return $this;
    }

    /**
     * Définit un regex de texte ([[:alpha:]]+) afin d'extraire un paramètre du chemin
     *
     * @param string $key
     * @param string $regex
     * @return Route
     */
    public function whereAlpha(string $key): Route {
        $this->where($key, "[[:alpha:]]+");
        return $this;
    }

    /**
     * Définit un regex alphanumérique ([[:alnum:]]+) afin d'extraire un paramètre du chemin
     *
     * @param string $key
     * @param string $regex
     * @return Route
     */
    public function whereAlNum(string $key): Route {
        $this->where($key, "[[:alnum:]]+");
        return $this;
    }

    /**
     * Créé une route en utilisant la méthode HTTP, le chemin cible et la fonction de controller donnée
     *
     * @param string $method
     * @param string $target
     * @param string $function
     * @return Route
     */
    private function using(string $method, string $target, string $function): Route {
        $this->method = strtoupper($method);

        if (!in_array($this->method, HttpMethod::allowed())) {
            throw new SystemException("Not implemented routing method '{$this->method}'", 500);
        }

        $this->function = $function;
        $this->target = $target;
        return $this;
    }
}
