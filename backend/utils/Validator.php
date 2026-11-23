<?php
require_once __DIR__ . '/exceptions.php';

class Validator
{
    /** Every field in $fields must exist and must not be empty. */
    public static function required($data, $fields)
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                throw new ValidationException("Field '$field' is required.");
            }
        }
    }

    public static function length($value, $field, $min, $max)
    {
        $length = mb_strlen(trim((string) $value));
        if ($length < $min || $length > $max) {
            throw new ValidationException("Field '$field' must be between $min and $max characters long.");
        }
    }

    /** A whole number; "5" from a form is accepted, "4.5" and "five" are not. */
    public static function integer($value, $field, $min = null, $max = null)
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new ValidationException("Field '$field' must be a whole number.");
        }
        self::range($value, $field, $min, $max);
    }

    /** A number that may have decimals (weight_kg, distance_km). */
    public static function number($value, $field, $min = null, $max = null)
    {
        if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            throw new ValidationException("Field '$field' must be a number.");
        }
        self::range($value, $field, $min, $max);
    }

    public static function one_of($value, $field, $allowed)
    {
        if (!in_array($value, $allowed, true)) {
            throw new ValidationException("Field '$field' must be one of: " . implode(', ', $allowed) . ".");
        }
    }

    public static function email($value, $field = 'email')
    {
        if (filter_var(trim((string) $value), FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationException("Field '$field' must be a valid email address.");
        }
    }

    /** A real calendar date in the format YYYY-MM-DD (2026-02-30 is rejected). */
    public static function date($value, $field)
    {
        $date = DateTime::createFromFormat('Y-m-d', (string) $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new ValidationException("Field '$field' must be a date in the format YYYY-MM-DD.");
        }
    }

    /** A workout or a record cannot happen in the future. Call date() first. */
    public static function not_in_future($value, $field)
    {
        if ($value > date('Y-m-d')) {
            throw new ValidationException("Field '$field' cannot be in the future.");
        }
    }

    /** At least 8 characters, with at least one letter and one number. */
    public static function password($value, $field = 'password')
    {
        $value = (string) $value;
        if (strlen($value) < 8 || !preg_match('/[A-Za-z]/', $value) || !preg_match('/[0-9]/', $value)) {
            throw new ValidationException("Field '$field' must have at least 8 characters, including a letter and a number.");
        }
    }

    /** Text with < or > could run as JavaScript when it is shown on a page (XSS). */
    public static function no_html($value, $field)
    {
        if (preg_match('/[<>]/', (string) $value)) {
            throw new ValidationException("Field '$field' must not contain HTML.");
        }
    }

    private static function range($value, $field, $min, $max)
    {
        if ($min !== null && $value < $min) {
            throw new ValidationException("Field '$field' must be at least $min.");
        }
        if ($max !== null && $value > $max) {
            throw new ValidationException("Field '$field' must be at most $max.");
        }
    }
}
