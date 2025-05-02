<?php

namespace Gzhegow\I18n\Type;

use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nWordInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Struct\I18nAwordInterface;
use Gzhegow\I18n\Language\I18nLanguageInterface;


class I18nType
{
    public static function language($value) : I18nLanguageInterface
    {
        return static::$manager->language($value);
    }

    public static function languageOrNull($value) : ?I18nLanguageInterface
    {
        return static::$manager->languageOrNull($value);
    }


    public static function lang($value) : I18nLangInterface
    {
        return static::$manager->lang($value);
    }

    public static function langOrNull($value) : ?I18nLangInterface
    {
        return static::$manager->langOrNull($value);
    }


    public static function group($group) : I18nGroupInterface
    {
        return static::$manager->group($group);
    }

    public static function groupOrNull($group) : ?I18nGroupInterface
    {
        return static::$manager->groupOrNull($group);
    }


    public static function aword($value) : I18nAwordInterface
    {
        return static::$manager->aword($value);
    }

    public static function awordOrNull($value) : ?I18nAwordInterface
    {
        return static::$manager->awordOrNull($value);
    }


    public static function word($value) : I18nWordInterface
    {
        return static::$manager->word($value);
    }

    public static function wordOrNull($value) : ?I18nWordInterface
    {
        return static::$manager->wordOrNull($value);
    }


    public static function setFacade(?I18nTypeInterface $type) : ?I18nTypeInterface
    {
        $last = static::$manager;

        static::$manager = $type;

        return $last;
    }

    /**
     * @var I18nTypeInterface
     */
    protected static $manager;
}
