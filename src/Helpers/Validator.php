<?php

namespace App\Helpers;

use App\Core\Validation;
use App\Exceptions\ApiException;
use App\Exceptions\SystemException;

/**
 * Permet de valider des données selon certaines règles
 * * Plusieurs règles, avec ou sans paramètres : "type" => [Validation::Required(), Validation::In([1, 2, 3])],
 * * Un seul paramètre : "date" => [Validation::Date("d.m.Y")],
 * * Une seule règle : "name" => Validation::Required(),
 */
class Validator {

    /**
     * Valide les données en fonction des Validations données
     *
     * @param array<Validation> $validations
     * @param ?array $data
     * @return array|throw
     */
    public static function validate(array $validations, array $data = null) {
        $data = empty($data) ? request()->methodData() : $data;

        $preparedData = self::prepareData($validations, $data);
        $executedValidations = self::executeValidations($validations, $preparedData);
        if (self::validationsPassed($executedValidations)) {
            return self::getFinalData($executedValidations);
        } else {
            self::throwValidationError($executedValidations);
        }
    }

    private static function getFinalData(array $validations): array {
        $finalData = [];
        foreach ($validations as $validation) {
            $finalData[$validation->getProperty()] = $validation->getReplacement() ?? $validation->getData();
        }
        return $finalData;
    }

    private static function validationsPassed(array $validations): bool {
        $passed = true;
        foreach ($validations as $validation) {
            $passed = $passed && $validation->getResult();
        }
        return $passed;
    }

    private static function prepareData(array $propertiesValidations, $data): array {
        foreach (array_keys($propertiesValidations) as $property) {
            $data[$property] ??= null;
        }
        return $data;
    }

    private static function executeValidations(array $propertiesValidations, $data): array {
        $allValidations = [];
        foreach ($propertiesValidations as $property => $propertyValidation) {
            if (is_integer($property)) {
                throw new SystemException("Validated property must have at least Validation::Nullable() as a rule", 500);
            }
            $validations = is_array($propertyValidation) ? $propertyValidation : [$propertyValidation];
            foreach ($validations as $validation) {
                $validation->execute($property, $data);
                if ($validation->getIsNull() && $validation->getStopWhenNull()) {
                    break;
                }
            }
            $allValidations = array_merge($allValidations, $validations);
        }
        return $allValidations;
    }

    private static function throwValidationError(array $validations) {
        $errors = [];
        foreach ($validations as $validation) {
            $errors = array_merge($errors, $validation->getErrors());
        }
        throw (new ApiException("Validation Error", 422))->setDetails($errors);
    }
}
