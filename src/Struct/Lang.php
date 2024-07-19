<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\I18n\Lib;


class Lang implements LangInterface
{
    /**
     * @var string
     */
    protected $value;


    private function __construct()
    {
    }

    public static function from($from) : self
    {
        return static::tryFrom($from);
    }

    public static function tryFrom($from) : ?self
    {
        $instance = null
            ?? static::tryFromInstance($from)
            ?? static::tryFromString($from);

        return $instance;
    }

    public static function tryFromInstance($from) : ?self
    {
        if (! is_a($from, static::class)) {
            return null;
        }

        return $from;
    }

    public static function tryFromString($from) : ?self
    {
        if (null === ($string = Lib::parse_string($from))) {
            return null;
        }

        if (! preg_match('/[a-z]+/', $string)) {
            return null;
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


    public function __debugInfo()
    {
        return get_object_vars($this);
    }
}
