<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\I18n;
use Gzhegow\I18n\I18nFacade;
use Gzhegow\I18n\Type\I18nType;
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
     * @return static|bool|null
     */
    public static function from($from, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? static::fromStatic($from, $cur)
            ?? static::fromString($from, $cur);

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
    public static function fromString($from, $ctx = null)
    {
        if (! Lib::type()->string_not_empty($fromString, $from)) {
            return Result::err(
                $ctx,
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

        return Result::ok($ctx, $instance);
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
