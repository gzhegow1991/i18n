<?php

namespace Gzhegow\I18n\Pool;

use Gzhegow\I18n\Lib;
use Gzhegow\I18n\Type\Type;


class PoolItem implements PoolItemInterface
{
    /**
     * @var string
     */
    protected $word;

    /**
     * @var string
     */
    protected $lang;
    /**
     * @var string
     */
    protected $group;

    /**
     * @var string
     */
    protected $phrase;
    /**
     * @var string[]
     */
    protected $choices = [];


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
            ?? static::tryFromArray($from);

        return $instance;
    }

    public static function tryFromInstance($from) : ?self
    {
        if (! is_a($from, static::class)) {
            return null;
        }

        return $from;
    }

    public static function tryFromArray($from) : ?self
    {
        if (! is_array($from)) {
            return null;
        }

        $word = $from[ 'word' ];
        $lang = $from[ 'lang' ];
        $phrase = $from[ 'phrase' ];
        $choices = $from[ 'choices' ];

        if (null === ($_word = Type::parseWord($word))) {
            return null;
        }

        if (null === ($_lang = Type::parseLang($lang))) {
            return null;
        }

        if (null === ($_phrase = Lib::parse_string($phrase))) {
            return null;
        }

        $_choices = null;
        if (! is_array($choices)) {
            return null;

        } else {
            foreach ( $choices as $i => $choice ) {
                if (null === ($_choice = Lib::parse_string($choice))) {
                    return null;
                }

                $_choices[ $i ] = $_choice;
            }
        }

        $_wordString = $_word->getValue();
        $_groupString = $_word->getGroup();
        $_langString = $_lang->getValue();

        $instance = new static();

        $instance->word = $_wordString;
        $instance->lang = $_langString;
        $instance->group = $_groupString;
        $instance->phrase = $_phrase;
        $instance->choices = $_choices;

        return $instance;
    }


    public function getWord() : string
    {
        return $this->word;
    }


    public function getLang() : string
    {
        return $this->lang;
    }

    public function getGroup() : string
    {
        return $this->group;
    }


    public function getPhrase() : string
    {
        return $this->phrase;
    }


    public function getChoice(int $n) : string
    {
        return $this->choices[ $n ];
    }

    /**
     * @return string[]
     */
    public function getChoices() : array
    {
        return $this->choices;
    }


    public function __debugInfo()
    {
        return get_object_vars($this);
    }
}
