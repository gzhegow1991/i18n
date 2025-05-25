<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\I18n;
use Gzhegow\I18n\I18nFacade;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\Lib\Modules\Php\Result\Ret;
use Gzhegow\Lib\Modules\Php\Result\Result;


class I18nAword implements I18nAwordInterface
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


    public function __toString()
    {
        return $this->value;
    }


    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function from($from, $ret = null)
    {
        $retCur = Result::asValue();

        $instance = null
            ?? static::fromStatic($from, $retCur)
            ?? static::fromString($from, $retCur);

        if ($retCur->isErr()) {
            return Result::err($ret, $retCur);
        }

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromStatic($from, $ret = null)
    {
        if ($from instanceof static) {
            return Result::ok($ret, $from);
        }

        return Result::err(
            $ret,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromString($from, $ret = null)
    {
        if (! Lib::type()->string_not_empty($fromString, $from)) {
            return Result::err(
                $ret,
                [ 'The `from` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $wordString = null;
        $groupString = null;
        if ($fromString[ 0 ] === I18n::AWORD_PREFIX) {
            $word = substr($fromString, 1);
            $word = I18nType::wordOrNull($word);

            if (null !== $word) {
                $wordString = $word->getValue();
                $groupString = $word->getGroup();
            }
        }

        $phrase = null;
        if (null === $wordString) {
            $phrase = $fromString;
        }

        $instance = new static();
        $instance->value = $fromString;
        $instance->word = $wordString;
        $instance->group = $groupString;
        $instance->phrase = $phrase;

        return Result::ok($ret, $instance);
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
