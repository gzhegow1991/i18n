<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Exception\LogicException;


class Group implements GroupInterface
{
    /**
     * @var string
     */
    protected $value;


    private function __construct()
    {
    }


    /**
     * @return static
     */
    public static function from($from) // : static
    {
        $instance = static::tryFrom($from, $error);

        if (null === $instance) {
            throw $error;
        }

        return $instance;
    }

    /**
     * @return static|null
     */
    public static function tryFrom($from, \Throwable &$last = null) // : ?static
    {
        $last = null;

        Lib::php_errors_start($b);

        $instance = null
            ?? static::tryFromInstance($from)
            ?? static::tryFromString($from);

        $errors = Lib::php_errors_end($b);

        if (null === $instance) {
            foreach ( $errors as $error ) {
                $last = new LogicException($error, null, $last);
            }
        }

        return $instance;
    }


    /**
     * @return static|null
     */
    public static function tryFromInstance($from) // : ?static
    {
        if (! is_a($from, static::class)) {
            return Lib::php_error(
                [
                    'The `from` should be instance of: ' . static::class,
                    $from,
                ]
            );
        }

        return $from;
    }

    /**
     * @return static|null
     */
    public static function tryFromString($from) // : ?static
    {
        if (null === ($string = Lib::parse_string_not_empty($from))) {
            return Lib::php_error(
                [
                    'The `from` should be non-empty string',
                    $from,
                ]
            );
        }

        if (! preg_match($regex = '/^[a-z][a-z0-9_-]*[a-z0-9]$/i', $string)) {
            return Lib::php_error(
                [
                    'The `from` should be string that is match regex: ' . $regex,
                    $from,
                ]
            );
        }

        $instance = new static();

        $instance->value = $string;

        return $instance;
    }


    public function __toString()
    {
        return $this->value;
    }


    public function getValue() : string
    {
        return $this->value;
    }
}
