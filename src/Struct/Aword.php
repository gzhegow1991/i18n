<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\I18n\Lib;
use Gzhegow\I18n\I18n;
use Gzhegow\I18n\Type\Type;


class Aword implements AwordInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $word;
    /**
     * @var string
     */
    protected $group;

    /**
     * @var string
     */
    protected $phrase;


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

        $wordString = null;
        $groupString = null;
        if ($string[ 0 ] === I18n::AWORD_PREFIX) {
            $word = substr($string, 1);
            $word = Type::parseWord($word);

            if ($word) {
                $wordString = $word->getValue();
                $groupString = $word->getGroup();
            }
        }

        $phrase = null;
        if (null === $wordString) {
            $phrase = $string;
        }

        $instance = new static();

        $instance->value = $string;

        $instance->word = $wordString;
        $instance->group = $groupString;

        $instance->phrase = $phrase;

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


    public function hasWord() : ?string
    {
        return $this->word;
    }

    public function getWord() : string
    {
        return $this->word;
    }


    public function hasGroup() : ?string
    {
        return $this->group;
    }

    public function getGroup() : string
    {
        return $this->group;
    }


    public function hasPhrase() : ?string
    {
        return $this->phrase;
    }

    public function getPhrase() : string
    {
        return $this->phrase;
    }


    public function __debugInfo()
    {
        return get_object_vars($this);
    }
}
