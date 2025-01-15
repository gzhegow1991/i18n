<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\I18n;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\I18n\Exception\LogicException;


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

        Lib::php()->errors_start($b);

        $instance = null
            ?? static::tryFromInstance($from)
            ?? static::tryFromString($from);

        $errors = Lib::php()->errors_end($b);

        if (null === $instance) {
            foreach ( $errors as $error ) {
                $last = new LogicException($error, $last);
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
            return Lib::php()->error(
                [ 'The `from` should be instance of: ' . static::class, $from ]
            );
        }

        return $from;
    }

    /**
     * @return static|null
     */
    public static function tryFromString($from) // : ?static
    {
        if (null === ($string = Lib::parse()->string_not_empty($from))) {
            return Lib::php()->error(
                [ 'The `from` should be non-empty string', $from ]
            );
        }

        $wordString = null;
        $groupString = null;
        if ($string[ 0 ] === I18n::AWORD_PREFIX) {
            $word = substr($string, 1);
            $word = I18nType::parseWord($word);

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
}
