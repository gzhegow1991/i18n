<?php

namespace Gzhegow\I18n\Repository\File\FileSource;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\Lib\Modules\Php\Result\Ret;
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
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function from($from, $ret = null)
    {
        $retCur = Result::asValue();

        $instance = null
            ?? static::fromStatic($from, $retCur)
            ?? static::fromArray($from, $retCur);

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
    public static function fromArray($from, $ret = null)
    {
        if (! is_array($from)) {
            return Result::err(
                $ret,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $filepath = $from[ 'filepath' ];
        $lang = $from[ 'lang' ];
        $group = $from[ 'group' ];

        if (! Lib::type()->filepath($filepathString, $filepath, true)) {
            return Result::err(
                $ret,
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

        return Result::ok($ret, $instance);
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
