<?php

namespace Gzhegow\I18n\Pool\PoolItem;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\I18n\Struct\I18nWordInterface;


class I18nPoolItem implements I18nPoolItemInterface
{
    /**
     * @var string
     */
    protected $lang;

    /**
     * @var I18nWordInterface
     */
    protected $word;
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


    public function toArray(array $options = []) : array
    {
        $vars = get_object_vars($this);

        if (null !== $this->word) {
            $vars[ 'word' ] = $this->word->toArray();
        }

        return $vars;
    }


    /**
     * @return static|Ret<static>
     */
    public static function from($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromArray($from)->orNull($ret);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromStatic($from, ?array $fallback = null)
    {
        if ($from instanceof static) {
            return Ret::ok($fallback, $from);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromArray($from, ?array $fallback = null)
    {
        if (! is_array($from)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $theType = Lib::type();

        $lang = $from[ 'lang' ];
        $word = $from[ 'word' ];

        $phrase = $from[ 'phrase' ];
        $choices = $from[ 'choices' ];

        if (null === ($langObject = I18nType::lang($lang))) {
            return Ret::throw(
                $fallback,
                [ 'The `from[phrase]` should be valid lang', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (null === ($wordObject = I18nType::word($word))) {
            return Ret::throw(
                $fallback,
                [ 'The `from[word]` should be valid word', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $theType->string_not_empty($phrase)->isOk([ &$phraseValid ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from[phrase]` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! is_array($choices)) {
            return Ret::throw(
                $fallback,
                [ 'The `from[choices]` should be array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $choiceValidList = null;
        foreach ( $choices as $i => $choice ) {
            if (! $theType->string_not_empty($choice)->isOk([ &$choiceValid ])) {
                return Ret::throw(
                    $fallback,
                    [ 'Each of `from[choices]` should be non-empty string', $from, $choice, $i ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $choiceValidList[ $i ] = $choiceValid;
        }

        $langString = $langObject->getValue();

        $instance = new static();

        $instance->lang = $langString;
        $instance->word = $wordObject;

        $instance->phrase = $phraseValid;
        $instance->choices = $choiceValidList;

        return Ret::ok($fallback, $instance);
    }


    public function getLang() : string
    {
        return $this->lang;
    }

    public function getWord() : I18nWordInterface
    {
        return $this->word;
    }


    public function getPhrase() : string
    {
        return $this->phrase;
    }

    /**
     * @return string[]
     */
    public function getChoices() : array
    {
        return $this->choices;
    }

    public function getChoice(int $n) : string
    {
        return $this->choices[ $n ];
    }
}
