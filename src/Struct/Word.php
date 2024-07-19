<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\I18n\Lib;
use Gzhegow\I18n\Type\Type;


class Word implements WordInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $group;


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

    public static function tryFromString($from)
    {
        if (null === ($string = Lib::parse_string($from))) {
            return null;
        }

        $regexPart = '[a-z][a-z0-9_-]*[a-z0-9]';

        if (! preg_match("/^{$regexPart}[.]{$regexPart}[.]{$regexPart}([_][\$]*)?$/", $string)) {
            return null;
        }

        [ $group ] = explode('.', $string, 2);

        if (null === ($group = Type::parseGroup($group))) {
            return null;
        }

        $groupString = $group->getValue();

        $instance = new static();

        $instance->value = $string;

        $instance->group = $groupString;

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


    public function getGroup() : string
    {
        return $this->group;
    }


    public function __debugInfo()
    {
        return get_object_vars($this);
    }
}
