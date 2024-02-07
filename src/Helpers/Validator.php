<?php

namespace App\Helpers;

use App\Exceptions\ApiException;
use App\Exceptions\SystemException;
use DateTime;
use ReflectionClass;

/**
 * Permet de valider des données selon certaines règles
 * * Plusieurs règles, avec ou sans paramètres : "type" => [Validator::Required, Validator::In => [1, 2, 3]],
 * * Un seul paramètre : "date" => [Validator::Date => "d.m.Y"],
 * * Une seule règle : "name" => Validator::Required,
 * * Aucune règle (nullable) : "description",
 */
class Validator {

    /** Doit être un tableau */
    public const Array = "array";
    /** Doit être un booleen */
    public const Bool = "bool";
    /** Doit être une date : [format (PHP Datetime)] */
    public const Date = "date";
    /** Doit être un domain */
    public const Domain = "domain";
    /** Doit être un email */
    public const Email = "email";
    /** Doit être un nombre à virgule */
    public const Float = "float";
    /** Doit être dans la liste : [liste d'éléments] */
    public const In = "in";
    /** Doit être un nombre entier */
    public const Integer = "integer";
    /** Doit être une adresse IP */
    public const IpAddress = "ip_address";
    /** Doit être un texte <= que : [taille maximale] */
    public const Length = "length";
    /** Doit être un chiffre <= que : [taille maximale] */
    public const Max = "max";
    /** Doit être une adresse Mac Address */
    public const MacAddress = "mac_address";
    /** Doit être un chiffre >= que : [taille minimale] */
    public const Min = "min";
    /** Peut être nul (par défaut si pas de règle de validation) */
    public const Nullable = "nullable";
    /** Doit être un nombre */
    public const Numeric = "numeric";
    /** Doit être présent */
    public const Required = "required";
    /** Doit être présent si un autre champs est absent : [autre champs] */
    public const RequiredIfAbsent = "required_if_absent";
    /** Doit être présent si un autre champs est présent : [autre champs] */
    public const RequiredIfPresent = "required_if_present";
    /** Doit être présent si aucun autre champs n'est présent : [autre champs, autre champs] */
    public const RequiredIfOthersAbsent = "required_if_others_absent";
    /** Doit être un texte */
    public const String = "string";
    /** Doit être une URL */
    public const Url = "url";

    private $errors = [];

    public function validate(array $validations, $data = null) {
        $data = empty($data) ? request()->methodData() : $data;
        $validationsResults = [];

        foreach ($validations as $property => $rules) {
            if (is_int($property)) {
                unset($validations[$property]);
                $validations[$rules] = null;
                $property = $rules;
            }

            if (!isset($data[$property])) {
                $data[$property] = null;
            }
        }

        foreach ($validations as $property => $rules) {
            $isNull = $data[$property] === null;
            $preparedRules = $this->prepareRules($rules);
            $requestedRules = array_keys($preparedRules);
            $isArray = in_array(self::Array, $requestedRules);
            $isDate = in_array(self::Date, $requestedRules);
            $isInteger = in_array(self::Integer, $requestedRules);

            if (!$isNull && !$isArray && !$isDate && !$isInteger) {
                $data[$property] = htmlspecialchars(stripslashes(trim($data[$property])));
            }

            $previousRule = null;
            foreach ($preparedRules as $ruleName => $ruleParameters) {
                if ($this->stopWhenNull($previousRule) && $isNull) {
                    break;
                }
                $validationsResults[$property][$ruleName] = $this->runValidation($ruleName, $ruleParameters, $property, $data);
                $previousRule = $ruleName;
            }
        }

        if ($this->passedAllValidations($validationsResults)) {
            return $data;
        } else {
            throw (new ApiException("Validation Error", 422))->setDetails($this->errors);
        }
    }

    private static function getAvailableRules() {
        $oClass = new ReflectionClass(get_called_class());
        return array_filter($oClass->getConstants(), fn ($element) => is_string($element));
    }

    private function stopWhenNull($rule) {
        return in_array($rule, [
            self::Required,
            self::RequiredIfAbsent,
            self::RequiredIfPresent,
            self::RequiredIfOthersAbsent,
            self::Nullable
        ]);
    }

    private function isReallySet($value) {
        return isset($value) && trim($value) !== "";
    }

    private function passedAllValidations($validationsResults) {
        $passed = true;
        foreach ($validationsResults as $validations) {
            foreach ($validations as $result) {
                if ($result !== true) {
                    $this->errors[] = $result;
                    $passed = false;
                }
            }
        }
        return $passed;
    }

    private function prepareRules($rules) {
        $preparedRules = [];
        if (empty($rules)) {
            $rules = [self::Nullable];
        }

        if (!is_array($rules)) {
            $rules = [$rules];
        }

        foreach ($rules as $ruleName => $ruleParameter) {
            if (is_integer($ruleName)) {
                $ruleName = $ruleParameter;
            }

            if (in_array($ruleName, self::getAvailableRules())) {
                $preparedRules[$ruleName] = is_array($ruleParameter) ? $ruleParameter : ($ruleName == $ruleParameter ? [] : [$ruleParameter]);
            } else {
                throw new SystemException("Validation rule {$ruleName} does not exists", 500);
            }
        }
        return $preparedRules;
    }

    private function runValidation($rule, $ruleParameters, $property, $data) {
        $result = $this->{"test_{$rule}"}($data[$property], $ruleParameters, $data);
        if ($result === false) {
            return [
                "property" => $property,
                "rule" => str_replace("_", " ", $rule) . (!empty($ruleParameters) ? " : " . implode(", ", $ruleParameters) : "")
            ];
        }
        return $result;
    }

    private function test_array($value) {
        return is_array($value);
    }

    private function test_bool($value) {
        return filter_var($value, FILTER_VALIDATE_BOOL) !== false;
    }

    private function test_date($value, $ruleParameters) {
        $format = $ruleParameters[0] ?? "Y-m-d";
        $dateTime = DateTime::createFromFormat($format, $value);
        return $dateTime && $dateTime->format($format) === $value;
    }

    private function test_domain($value) {
        return filter_var($value, FILTER_VALIDATE_DOMAIN) !== false;
    }

    private function test_email($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function test_float($value) {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    private function test_integer($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function test_in($value, $ruleParameters) {
        return in_array($value, $ruleParameters);
    }

    private function test_ip_address($value) {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    private function test_length($value, $ruleParameters) {
        return strlen((string)$value) <= $ruleParameters[0];
    }

    private function test_max($value, $ruleParameters) {
        return $value <= $ruleParameters[0];
    }

    private function test_mac_address($value) {
        return filter_var($value, FILTER_VALIDATE_MAC) !== false;
    }

    private function test_min($value, $ruleParameters) {
        return $value >= $ruleParameters[0];
    }

    private function test_nullable() {
        return true;
    }

    private function test_numeric($value) {
        return is_numeric($value);
    }

    private function test_required($value) {
        return $this->isReallySet($value);
    }

    private function test_required_if_absent($value, $others, $data) {
        return $this->isReallySet($value) || // celui-ci est présent
            $this->isReallySet($data[$others[0]]); // ou l'autre est présent
    }

    private function test_required_if_present($value, $others, $data) {
        return !$this->isReallySet($data[$others[0]]) // l'autre n'est pas présent
            || ($this->isReallySet($data[$others[0]]) && $this->isReallySet($value)); // ou il est présent et celui-ci aussi
    }

    private function test_required_if_others_absent($value, $others, $data) {
        return $this->isReallySet($value) // celui-ci est présent
            || array_reduce($others, fn ($acc, $other) => $acc || $this->isReallySet($data[$other]), false); // ou un autre est présent
    }

    private function test_string($value) {
        return is_string($value);
    }

    private function test_url($value) {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}
