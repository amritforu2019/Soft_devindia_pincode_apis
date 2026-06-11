<?php

declare(strict_types=1);

final class Validator
{
    public static function action(?string $action): string
    {
        $allowed = ['states', 'cities', 'areas', 'pincode', 'search'];

        if ($action === null || $action === '') {
            throw new InvalidArgumentException('Action parameter is required.');
        }

        $action = strtolower(trim($action));

        if (!in_array($action, $allowed, true)) {
            throw new InvalidArgumentException('Invalid action parameter.');
        }

        return $action;
    }

    public static function positiveInt(?string $value, string $fieldName): int
    {
        if ($value === null || $value === '') {
            throw new InvalidArgumentException($fieldName . ' is required.');
        }

        if (!ctype_digit($value)) {
            throw new InvalidArgumentException('Invalid ' . $fieldName . '.');
        }

        $intValue = (int) $value;

        if ($intValue <= 0) {
            throw new InvalidArgumentException('Invalid ' . $fieldName . '.');
        }

        return $intValue;
    }

    public static function pincode(?string $value): string
    {
        if ($value === null || $value === '') {
            throw new InvalidArgumentException('Pincode is required.');
        }

        $pin = trim($value);

        if (!preg_match('/^\d{6}$/', $pin)) {
            throw new InvalidArgumentException('Invalid pincode format.');
        }

        return $pin;
    }

    /**
     * @return array{type: string, id: int}
     */
    public static function searchId(array $queryParams): array
    {
        $stateId = $queryParams['state_id'] ?? null;
        $cityId = $queryParams['city_id'] ?? null;
        $areaId = $queryParams['area_id'] ?? null;

        $provided = [];

        if ($stateId !== null && $stateId !== '') {
            $provided['state_id'] = self::positiveInt((string) $stateId, 'state_id');
        }

        if ($cityId !== null && $cityId !== '') {
            $provided['city_id'] = self::positiveInt((string) $cityId, 'city_id');
        }

        if ($areaId !== null && $areaId !== '') {
            $provided['area_id'] = self::positiveInt((string) $areaId, 'area_id');
        }

        if ($provided === []) {
            throw new InvalidArgumentException('Provide exactly one ID: state_id, city_id, or area_id.');
        }

        if (count($provided) > 1) {
            throw new InvalidArgumentException('Provide only one ID parameter at a time.');
        }

        $field = array_key_first($provided);

        return [
            'type' => (string) $field,
            'id'   => $provided[$field],
        ];
    }
}
