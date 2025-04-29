<?php

namespace Gzhegow\I18n\Pool;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\Lib\Modules\Php\Result\Result;


class I18nPoolItem implements I18nPoolItemInterface
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
     * @return static|bool|null
     */
    public static function from($from, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? static::fromStatic($from, $cur)
            ?? static::fromArray($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromStatic($from, $ctx = null)
    {
        if ($from instanceof static) {
            return Result::ok($ctx, $from);
        }

        return Result::err(
            $ctx,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromArray($from, $ctx = null)
    {
        if (! is_array($from)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $theType = Lib::type();

        $word = $from[ 'word' ];
        $lang = $from[ 'lang' ];
        $phrase = $from[ 'phrase' ];
        $choices = $from[ 'choices' ];

        $wordObject = I18nType::word($word);
        $langObject = I18nType::lang($lang);

        if (! $theType->string_not_empty($phraseString, $phrase)) {
            return Result::err(
                $ctx,
                [ 'The `from[phrase]` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! is_array($choices)) {
            return Result::err(
                $ctx,
                [ 'The `from[choices]` should be array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $choiceStringList = null;
        foreach ( $choices as $i => $choice ) {
            if (! $theType->string_not_empty($choiceString, $choice)) {
                return Result::err(
                    $ctx,
                    [ 'Each of `from[choices]` should be non-empty string', $from, $choice, $i ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $choiceStringList[ $i ] = $choiceString;
        }

        $wordString = $wordObject->getValue();
        $groupString = $wordObject->getGroup();
        $langString = $langObject->getValue();

        $instance = new static();
        $instance->word = $wordString;
        $instance->group = $groupString;
        $instance->lang = $langString;
        $instance->phrase = $phraseString;
        $instance->choices = $choiceStringList;

        return Result::ok($ctx, $instance);
    }


    public function getWord() : string
    {
        return $this->word;
    }

    /**
     * @return static
     */
    public function setWord(string $word)
    {
        $clone = clone $this;
        $clone->word = I18nType::word($word)->getValue();

        return $clone;
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
