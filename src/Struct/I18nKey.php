<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;


class I18nKey implements I18nKeyInterface
{
    /**
     * @var string
     */
    protected $value;


    private function __construct()
    {
    }


    public function __toString()
    {
        return $this->toString();
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
        $theType = Lib::type();

        if (! $theType->string_not_empty($from)->isOk([ &$fromString ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $keyString = $fromString;

        $regexKey = '/^' . static::getRegex() . '$/';

        if (! preg_match($regexKey, $keyString)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be string that is match regex: ' . $regexKey, $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();
        $instance->value = $fromString;

        return Ret::ok($fallback, $instance);
    }


    public function getValue() : string
    {
        return $this->value;
    }


    public static function getRegex() : string
    {
        return '[a-zA-Z][a-zA-Z0-9_-]*[a-zA-Z0-9]([_][\$]*)?';
    }
}
