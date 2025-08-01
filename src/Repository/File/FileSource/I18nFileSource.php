<?php

namespace Gzhegow\I18n\Repository\File\FileSource;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\Lib\Modules\Type\Ret;


class I18nFileSource implements I18nFileSourceInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $realpath;

    /**
     * @var string
     */
    protected $lang;
    /**
     * @var string
     */
    protected $group;


    private function __construct()
    {
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

        $filepath = $from[ 'filepath' ];
        $lang = $from[ 'lang' ];
        $group = $from[ 'group' ];

        if (! $theType->filepath($filepath, true)->isOk([ &$filepathValid ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from[filepath]` should be valid path', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (null === ($langObject = I18nType::lang($lang))) {
            return Ret::throw(
                $fallback,
                [ 'The `from[lang]` should be valid lang', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (null === ($groupObject = I18nType::group($group))) {
            return Ret::throw(
                $fallback,
                [ 'The `from[group]` should be valid group', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $langString = $langObject->getValue();
        $groupString = $groupObject->getValue();

        $instance = new static();
        $instance->value = $filepathValid;
        $instance->lang = $langString;
        $instance->group = $groupString;
        $instance->realpath = $filepathValid;

        return Ret::ok($fallback, $instance);
    }


    public function getValue() : string
    {
        return $this->value;
    }


    public function getLang() : string
    {
        return $this->lang;
    }

    public function getGroup() : string
    {
        return $this->group;
    }


    public function hasRealpath() : ?string
    {
        return $this->realpath;
    }

    public function getRealpath() : string
    {
        return $this->realpath;
    }
}
