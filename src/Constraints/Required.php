<?php

namespace League\JsonGuard\Constraints;

use League\JsonGuard\Assert;
use League\JsonGuard\ValidationError;

class Required implements PropertyConstraint
{
    const KEYWORD = 'required';

    /**
     * {@inheritdoc}
     */
    public static function validate($data, $parameter, $pointer = null)
    {
        Assert::type($parameter, 'array', self::KEYWORD, $pointer);
        Assert::notEmpty($parameter, self::KEYWORD, $pointer);

        if (!is_object($data)) {
            return null;
        }

        $actualProperties = array_keys(get_object_vars($data));
        $missing          = array_diff($parameter, $actualProperties);

        if (count($missing)) {
            $errors = [];

            foreach ($missing as $attr) {
                $errors[] = new ValidationError(
                    'O valor para o campo: {attr} não foi informado',
                    self::KEYWORD,
                    $data,
                    $pointer,
                    ['attr' => $attr]
                );
            }

            return $errors;
        }

        return null;
    }
}
