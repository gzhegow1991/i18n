<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\I18n;
use Gzhegow\Lib\Modules\Type\Ret;


class I18nWord implements I18nWordInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $group;
    /**
     * @var string
     */
    protected $section;
    /**
     * @var string
     */
    protected $key;


    private function __construct()
    {
    }


    public function __toString()
    {
        return $this->toString();
    }


    public function toArray(array $options = []) : array
    {
        return get_object_vars($this);
    }

    public function toString(array $options = []) : string
    {
        return $this->value;
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
        $thePreg = Lib::preg();
        $theType = Lib::type();

        if (! $theType->string_not_empty($from)->isOk([ &$fromString ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $wordString = $fromString;

        $regexWordSeparator = $thePreg->preg_quote_ord(I18n::WORD_SEPARATOR);

        $regexGroup = I18nGroup::getRegex();
        $regexSection = I18nSection::getRegex();
        $regexKey = I18nKey::getRegex();

        $regex = ''
            . '/^'
            . $regexGroup
            . $regexWordSeparator
            . $regexSection
            . $regexWordSeparator
            . $regexKey
            . '$/';

        if (! preg_match($regex, $wordString)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be string that match regex: ' . $regex, $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $split = preg_split('/' . $regexWordSeparator . '/', $fromString, 3);

        [ $groupString, $sectionString, $keyString ] = $split;

        $instance = new static();
        $instance->value = $fromString;

        $instance->group = $groupString;
        $instance->section = $sectionString;
        $instance->key = $keyString;

        return Ret::ok($fallback, $instance);
    }


    public function getValue() : string
    {
        return $this->value;
    }


    public function getGroup() : string
    {
        return $this->group;
    }


    public function getSectionKey() : string
    {
        return $this->section . I18n::WORD_SEPARATOR . $this->key;
    }

    public function getSection() : string
    {
        return $this->section;
    }

    public function getKey() : string
    {
        return $this->key;
    }
}
