<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\I18n;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\Lib\Modules\Type\Ret;


class I18nAword implements I18nAwordInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var I18nWordInterface
     */
    protected $word;
    /**
     * @var string
     */
    protected $phrase;


    private function __construct()
    {
    }


    public function __toString()
    {
        return $this->toString();
    }


    public function toArray(array $options = []) : array
    {
        $vars = get_object_vars($this);

        if (null !== $this->word) {
            $vars[ 'word' ] = $this->word->toArray();
        }

        return $vars;
    }

    public function toString(array $options = []) : string
    {
        return $this->word
            ? $this->word->toString($options)
            : $this->phrase;
    }


    /**
     * @return static|Ret<static>
     */
    public static function from($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromString($from)->orNull($ret);

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
    public static function fromString($from, ?array $fallback = null)
    {
        $theStr = Lib::str();
        $theType = Lib::type();

        if (! $theType->string_not_empty($from)->isOk([ &$fromString ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $phraseString = null;
        $wordObject = null;
        if (true
            && $theStr->str_starts($fromString, I18n::AWORD_PREFIX, false, [ &$substr ])
            && (null !== ($wordValid = I18nType::wordOrNull($substr)))
        ) {
            $wordObject = $wordValid;

        } else {
            $phraseString = $fromString;
        }

        $instance = new static();
        $instance->value = $fromString;

        $instance->word = $wordObject;
        $instance->phrase = $phraseString;

        return Ret::ok($fallback, $instance);
    }


    public function getValue() : string
    {
        return $this->value;
    }


    public function getWordOrPhrase() : string
    {
        return $this->value;
    }


    /**
     * @param I18nWordInterface $refWord
     */
    public function isWord(&$refWord = null) : bool
    {
        $refWord = null;

        if (null !== $this->word) {
            $refWord = $this->word;

            return true;
        }

        return false;
    }

    public function getWord() : I18nWordInterface
    {
        return $this->word;
    }


    /**
     * @param string $refPhrase
     */
    public function isPhrase(&$refPhrase = null) : bool
    {
        $refPhrase = null;

        if (null !== $this->phrase) {
            $refPhrase = $this->phrase;

            return true;
        }

        return false;
    }

    public function getPhrase() : string
    {
        return $this->phrase;
    }
}
