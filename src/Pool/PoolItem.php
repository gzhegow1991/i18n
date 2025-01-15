<?php

namespace Gzhegow\I18n\Pool;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\I18n\Exception\LogicException;


class PoolItem implements I18nPoolItemInterface
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
            ?? static::tryFromArray($from);

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
    public static function tryFromArray($from) // : ?static
    {
        if (! is_array($from)) {
            return Lib::php()->error(
                [ 'The `from` should be array', $from ]
            );
        }

        $word = $from[ 'word' ];
        $lang = $from[ 'lang' ];
        $phrase = $from[ 'phrase' ];
        $choices = $from[ 'choices' ];

        if (null === ($_word = I18nType::parseWord($word))) {
            return Lib::php()->error(
                [ 'The `from[word]` should be valid word', $from ]
            );
        }

        if (null === ($_lang = I18nType::parseLang($lang))) {
            return Lib::php()->error(
                [ 'The `from[lang]` should be valid lang', $from ]
            );
        }

        if (null === ($_phrase = Lib::parse()->string_not_empty($phrase))) {
            return Lib::php()->error(
                [ 'The `from[phrase]` should be non-empty string', $from ]
            );
        }

        $_choices = null;
        if (! is_array($choices)) {
            return null;

        } else {
            foreach ( $choices as $i => $choice ) {
                if (null === ($_choice = Lib::parse()->string_not_empty($choice))) {
                    return Lib::php()->error(
                        [ 'Each of `from[choices]` should be non-empty string', $from, $choice, $i ]
                    );
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
}
