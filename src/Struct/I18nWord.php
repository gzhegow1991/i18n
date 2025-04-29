<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\Lib\Modules\Php\Result\Result;


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

        $regexPart = '[a-z][a-z0-9_-]*[a-z0-9]';
        $regex = ''
            . '/^'
            . $regexPart
            . '[.]'
            . $regexPart
            . '[.]'
            . $regexPart
            . '([_][\$]*)?'
            . '$/';

        if (! preg_match($regex, $fromString)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be string that match regex: ' . $regex, $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        [ $group ] = explode('.', $fromString, 2);

        $groupObject = I18nType::group($group);

        $groupString = $groupObject->getValue();

        $instance = new static();
        $instance->value = $fromString;
        $instance->group = $groupString;

        return Result::ok($ctx, $instance);
    }


    public function getValue() : string
    {
        return $this->value;
    }


    public function getGroup() : string
    {
        return $this->group;
    }
}
