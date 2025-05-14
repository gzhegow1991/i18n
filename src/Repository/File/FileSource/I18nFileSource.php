<?php

namespace Gzhegow\I18n\Repository\File\FileSource;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\Lib\Modules\Php\Result\Result;


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

        $filepath = $from[ 'filepath' ];
        $lang = $from[ 'lang' ];
        $group = $from[ 'group' ];

        if (! Lib::type()->filepath($filepathString, $filepath, true)) {
            return Result::err(
                $ctx,
                [ 'The `from[filepath]` should be valid path', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $langObject = I18nType::lang($lang);
        $groupObject = I18nType::group($group);

        $langString = $langObject->getValue();
        $groupString = $groupObject->getValue();

        $instance = new static();
        $instance->value = $filepathString;
        $instance->lang = $langString;
        $instance->group = $groupString;
        $instance->realpath = $filepathString;

        return Result::ok($ctx, $instance);
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
