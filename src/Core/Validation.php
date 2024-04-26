<?php

namespace App\Core;

use DateTime;

class Validation {

    private const STOP_WHEN_NULL = true;

    private $allData = null;
    private $data = null;
    private $default = null;
    private $errors = [];
    private $isNull = false;
    private $name = null;
    private $parameters = null;
    private $property = null;
    private $replacement = null;
    private $result = true;
    private $skipped = true;
    private $stopWhenNull = false;

    private function __construct($name, $parameters, $default, $stopWhenNull = false) {
        $this->name = $name;
        $this->parameters = $this->prepareParameters($parameters);
        $this->default = $default;
        $this->stopWhenNull = $stopWhenNull;
    }

    public function getData() {
        return $this->data;
    }

    public function getAllData() {
        return $this->allData;
    }

    public function getDefault() {
        return $this->default;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getStopWhenNull() {
        return $this->stopWhenNull;
    }

    public function getIsNull() {
        return $this->isNull;
    }

    public function getName() {
        return $this->name;
    }

    public function getParameters() {
        return $this->parameters;
    }

    public function getProperty() {
        return $this->property;
    }

    public function getReplacement() {
        return $this->replacement;
    }

    public function getResult() {
        return $this->result;
    }

    public function getSkiped() {
        return $this->skipped;
    }

    public function setData($allData) {
        $this->allData ??= $allData;
        return $this;
    }

    public function setProperty($property) {
        $this->property ??= $property;
        return $this;
    }

    private static function toValidation($name, $parameters = null, $default = null, $stopWhenNull = false) {
        return new self($name, $parameters, $default, $stopWhenNull);
    }

    public function execute($property = null, $data = null) {
        $this->setProperty($property);
        $this->setData($data);

        $this->data = $this->allData[$this->property] ?? $this->default;
        $this->isNull = $this->data === null;
        $this->result = $this->{"test_{$this->name}"}($this->data, $this->parameters);

        if ($this->result === false) {
            $this->errors[] = [
                "property" => $this->property,
                "rule" => str_replace("_", " ", $this->name) . (!empty($this->parameters) ? " : " . implode(", ", $this->parameters) : "")
            ];
        }

        $this->skipped = false;
        return $this;
    }

    private function prepareParameters($parameters) {
        $parameters = $parameters === null ? [] : $parameters;
        return is_array($parameters) ? $parameters : [$parameters];
    }

    /** Doit être un tableau */
    public static function Array($parameters, $default = null) {
        return self::toValidation("array", $parameters, $default);
    }

    /** Doit être un nombre entre : [taille minimale, taille maximale] */
    public static function Between($parameters, $default = null) {
        return self::toValidation("between", $parameters, $default);
    }

    /** Doit être un booleen */
    public static function Bool($default = null) {
        return self::toValidation("bool", null, $default);
    }

    /** Doit être une date : [format (PHP Datetime)] */
    public static function Date($parameters, $default = null) {
        return self::toValidation("date", $parameters, $default);
    }

    /** Doit être un domain */
    public static function Domain($default = null) {
        return self::toValidation("domain", null, $default);
    }

    /** Doit être un email */
    public static function Email($default = null) {
        return self::toValidation("email", null, $default);
    }

    /** Doit être un nombre à virgule */
    public static function File($default = null) {
        return self::toValidation("file", null, $default);
    }

    /** Doit être un nombre à virgule */
    public static function Float($default = null) {
        return self::toValidation("float", null, $default);
    }

    /** Doit être dans la liste : [liste d'éléments] */
    public static function In($parameters, $default = null) {
        return self::toValidation("in", $parameters, $default);
    }

    /** Doit être un nombre entier */
    public static function Integer($default = null) {
        return self::toValidation("integer", null, $default);
    }

    /** Doit être une adresse IP */
    public static function IpAddress($default = null) {
        return self::toValidation("ip_address", null, $default);
    }

    /** Doit être un texte, tableau ou nombre de : [taille requise] */
    public static function Length($parameters, $default = null) {
        return self::toValidation("length", $parameters, $default);
    }

    /** Doit être un texte, tableau ou nombre <= que : [taille maximale] */
    public static function Max($parameters, $default = null) {
        return self::toValidation("max", $parameters, $default);
    }

    /** Doit être une adresse Mac Address */
    public static function MacAddress($default = null) {
        return self::toValidation("mac_address", null, $default);
    }

    /** Doit être un fichier de type dans la liste */
    public static function Mime($parameters, $default = null) {
        return self::toValidation("mime", $parameters, $default);
    }

    /** Doit être un texte, tableau ou nombre >= que : [taille minimale] */
    public static function Min($parameters, $default = null) {
        return self::toValidation("min", $parameters, $default);
    }

    /** Peut être nul */
    public static function Nullable($default = null) {
        return self::toValidation("nullable", null, $default);
    }

    /** Doit être présent */
    public static function Required($default = null) {
        return self::toValidation("required", null, $default, self::STOP_WHEN_NULL);
    }

    /** Doit être présent si un autre champs est absent : [autre champs] */
    public static function RequiredIfAbsent($parameters, $default = null) {
        return self::toValidation("required_if_absent", $parameters, $default, self::STOP_WHEN_NULL);
    }

    /** Doit être présent si un autre champs est présent : [autre champs] */
    public static function RequiredIfPresent($parameters, $default = null) {
        return self::toValidation("required_if_present", $parameters, $default, self::STOP_WHEN_NULL);
    }

    /** Doit être présent si aucun autre champs n'est présent : [autre champs, autre champs] */
    public static function RequiredIfOthersAbsent($parameters, $default = null) {
        return self::toValidation("required_if_others_absent", $parameters, $default, self::STOP_WHEN_NULL);
    }

    /** Doit être un texte */
    public static function String($parameters, $default = null) {
        return self::toValidation("string", $parameters, $default);
    }

    /** Doit être une URL */
    public static function Url($parameters, $default = null) {
        return self::toValidation("url", $parameters, $default);
    }

    private function isReallySet($value) {
        $isSet = isset($value);
        if (is_array($value)) {
            $isSet = !empty($value);
        } else {
            $isSet = trim($value) !== "";
        }
        return $isSet;
    }

    private function test_array($value) {
        return is_array($value);
    }

    private function test_between($value, $ruleParameters) {
        return $value >= $ruleParameters[0] && $value <= $ruleParameters[1];
    }

    private function test_bool($value) {
        $result = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if ($result !== null) {
            $this->replacement = $result;
        }
        return $result !== null;
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

    private function test_file($value) {
        return is_array($value)
            && !empty($value["name"])
            && !empty($value["type"])
            && !empty($value["tmp_name"])
            && isset($value["error"]) && $value["error"] === 0
            && !empty($value["size"]);
    }

    private function test_float($value) {
        $result = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($result !== false) {
            $this->replacement = $result;
        }
        return $result !== false;
    }

    private function test_integer($value) {
        $result = filter_var($value, FILTER_VALIDATE_INT);
        if ($result !== false) {
            $this->replacement = $result;
        }
        return $result !== false;
    }

    private function test_in($value, $ruleParameters) {
        return in_array($value, $ruleParameters);
    }

    private function test_ip_address($value) {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    private function test_length($value, $ruleParameters) {
        return (
            is_string($value) ? strlen($value) : (is_array($value) ? count($value) : $value)
        ) == $ruleParameters[0];
    }

    private function test_max($value, $ruleParameters) {
        return (
            is_string($value) ? strlen($value) : (is_array($value) ? count($value) : $value)
        ) <= $ruleParameters[0];
    }

    private function test_mac_address($value) {
        return filter_var($value, FILTER_VALIDATE_MAC) !== false;
    }

    private function test_mime($value, $ruleParameters) {
        return $this->test_in(mime_content_type($value["tmp_name"]), $ruleParameters);
    }

    private function test_min($value, $ruleParameters) {
        return (
            is_string($value) ? strlen($value) : (is_array($value) ? count($value) : $value)
        ) >= $ruleParameters[0];
    }

    private function test_nullable() {
        return true;
    }

    private function test_required($value) {
        return $this->isReallySet($value);
    }

    private function test_required_if_absent($value, $others) {
        return $this->isReallySet($value) // celui-ci est présent
            || $this->isReallySet($this->allData[$others[0]] ?? null); // ou l'autre est présent
    }

    private function test_required_if_present($value, $others) {
        return !$this->isReallySet($this->allData[$others[0]] ?? null) // l'autre n'est pas présent
            || ($this->isReallySet($this->allData[$others[0]] ?? null) && $this->isReallySet($value)); // ou il est présent et celui-ci aussi
    }

    private function test_required_if_others_absent($value, $others) {
        return $this->isReallySet($value) // celui-ci est présent
            || array_reduce($others, fn ($acc, $other) => $acc || $this->isReallySet($this->allData[$other] ?? null), false); // ou un autre est présent
    }

    private function test_string($value) {
        return is_string($value);
    }

    private function test_url($value) {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}
