<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;

class Format implements PropertyConstraint
{
    const KEYWORD = 'format';

    // @codingStandardsIgnoreStart
    // @see https://www.w3.org/TR/2012/REC-xmlschema11-2-20120405/datatypes.html#dateTime-lexical-mapping
    const DATE_TIME_PATTERN = '/^-?([1-9][0-9]{3,}|0[0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])T(([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9](\.[0-9]+)?|(24:00:00(\.0+)?))(Z|(\+|-)((0[0-9]|1[0-3]):[0-5][0-9]|14:00))?$/';
    // @codingStandardsIgnoreEnd

    const HOST_NAME_PATTERN = '/^[_a-z]+\.([_a-z]+\.?)+$/i';

    /**
     * {@inheritdoc}
     */
    public static function validate($value, $parameter, $pointer = null)
    {
        Assert::type($parameter, 'string', self::KEYWORD, $pointer);

        switch ($parameter) {
            case 'date-time':
                return self::validateRegex(
                    $parameter,
                    $value,
                    self::DATE_TIME_PATTERN,
                    $pointer
                );
            case 'uri':
                return self::validateFilter(
                    $parameter,
                    $value,
                    FILTER_VALIDATE_URL,
                    null,
                    $pointer
                );
            case 'email':
                return self::validateFilter(
                    $parameter,
                    $value,
                    FILTER_VALIDATE_EMAIL,
                    null,
                    $pointer
                );
            case 'ipv4':
                return self::validateFilter(
                    $parameter,
                    $value,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_IPV4,
                    $pointer
                );
            case 'ipv6':
                return self::validateFilter(
                    $parameter,
                    $value,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_IPV6,
                    $pointer
                );
            case 'hostname':
                return self::validateRegex(
                    $parameter,
                    $value,
                    self::HOST_NAME_PATTERN,
                    $pointer
                );
        }
    }

    /**
     * @param string $format
     * @param mixed $value
     * @param string $pattern
     * @param string $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    private static function validateRegex($format, $value, $pattern, $pointer)
    {
        if (!is_string($value) || preg_match($pattern, $value) === 1) {
            return null;
        }

        return new ValidationError(
            'O valor {value} deve estar no formato {format}',
            self::KEYWORD,
            $value,
            $pointer,
            ['value' => $value, 'format' => $format]
        );
    }

    /**
     * @param string $format
     * @param mixed  $value
     * @param int    $filter
     * @param mixed  $options
     * @param string $pointer
     *
     * @return \League\JsonGuard\ValidationError|null
     */
    private static function validateFilter($format, $value, $filter, $options, $pointer)
    {
        if (!is_string($value) || filter_var($value, $filter, $options) !== false) {
            return null;
        }

        // This workaround allows otherwise valid protocol relative urls to pass.
        // @see https://bugs.php.net/bug.php?id=72301
        if ($filter === FILTER_VALIDATE_URL && is_string($value) && strpos($value, '//') === 0) {
            if (filter_var('http:' . $value, $filter, $options) !== false) {
                return null;
            }
        }

        return new ValidationError(
            'O valor {value} não está no formato {format}',
            self::KEYWORD,
            $value,
            $pointer,
            ['value' => $value, 'format' => $format]
        );
    }
}
