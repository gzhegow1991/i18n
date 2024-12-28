<?php

namespace Gzhegow\I18n\Repository\File\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\I18n\Exception\LogicException;


class FileSource implements FileSourceInterface
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
     * @return static
     */
    public static function from($from) // : static
    {
        $instance = static::tryFrom($from, $error);

        if (null === $instance) {
            throw $error;
        }

        return $instance;
    }

    /**
     * @return static|null
     */
    public static function tryFrom($from, \Throwable &$last = null) // : ?static
    {
        $last = null;

        Lib::php()->errors_start($b);

        $instance = null
            ?? static::tryFromInstance($from)
            ?? static::tryFromArray($from);

        $errors = Lib::php()->errors_end($b);

        if (null === $instance) {
            foreach ( $errors as $error ) {
                $last = new LogicException($error, null, $last);
            }
        }

        return $instance;
    }


    /**
     * @return static|null
     */
    public static function tryFromInstance($from) // : ?static
    {
        if (! is_a($from, static::class)) {
            return Lib::php()->error(
                [
                    'The `from` should be instance of: ' . static::class,
                    $from,
                ]
            );
        }

        return $from;
    }

    /**
     * @return static|null
     */
    public static function tryFromArray($from) // : ?static
    {
        if (! is_array($from)) {
            return Lib::php()->error(
                [
                    'The `from` should be array',
                    $from,
                ]
            );
        }

        $path = $from[ 'path' ];
        $lang = $from[ 'lang' ];
        $group = $from[ 'group' ];

        if (null === ($_path = Lib::parse()->path($path))) {
            return null;
        }

        $lang = I18nType::theLang($lang);
        $group = I18nType::theGroup($group);

        $realpath = null;
        if (is_file($_path)) {
            $realpath = realpath($_path);
        }

        $langString = $lang->getValue();
        $groupString = $group->getValue();

        $instance = new static();

        $instance->value = $_path;

        $instance->lang = $langString;
        $instance->group = $groupString;

        $instance->realpath = $realpath;

        return $instance;
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
