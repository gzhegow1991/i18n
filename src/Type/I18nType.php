<?php

namespace Gzhegow\I18n\Type;

use Gzhegow\I18n\Struct\I18nKeyInterface;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nWordInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Struct\I18nAwordInterface;
use Gzhegow\I18n\Struct\I18nLocaleInterface;
use Gzhegow\I18n\Pool\PoolItem\I18nPoolItemInterface;
use Gzhegow\I18n\Struct\I18nSectionInterface;
use Gzhegow\I18n\Language\I18nLanguageInterface;


class I18nType
{
    public static function poolItem($value) : I18nPoolItemInterface
    {
        return static::getInstance()->poolItem($value);
    }

    public static function poolItemOrNull($value) : ?I18nPoolItemInterface
    {
        return static::getInstance()->poolItemOrNull($value);
    }


    public static function language($value) : I18nLanguageInterface
    {
        return static::getInstance()->language($value);
    }

    public static function languageOrNull($value) : ?I18nLanguageInterface
    {
        return static::getInstance()->languageOrNull($value);
    }


    public static function lang($value) : I18nLangInterface
    {
        return static::getInstance()->lang($value);
    }

    public static function langOrNull($value) : ?I18nLangInterface
    {
        return static::getInstance()->langOrNull($value);
    }


    public static function locale($value) : I18nLocaleInterface
    {
        return static::getInstance()->locale($value);
    }

    public static function localeOrNull($value) : ?I18nLocaleInterface
    {
        return static::getInstance()->localeOrNull($value);
    }


    public static function aword($value) : I18nAwordInterface
    {
        return static::getInstance()->aword($value);
    }

    public static function awordOrNull($value) : ?I18nAwordInterface
    {
        return static::getInstance()->awordOrNull($value);
    }


    public static function word($value) : I18nWordInterface
    {
        return static::getInstance()->word($value);
    }

    public static function wordOrNull($value) : ?I18nWordInterface
    {
        return static::getInstance()->wordOrNull($value);
    }


    public static function group($group) : I18nGroupInterface
    {
        return static::getInstance()->group($group);
    }

    public static function groupOrNull($group) : ?I18nGroupInterface
    {
        return static::getInstance()->groupOrNull($group);
    }


    public static function section($group) : I18nSectionInterface
    {
        return static::getInstance()->section($group);
    }

    public static function sectionOrNull($group) : ?I18nSectionInterface
    {
        return static::getInstance()->sectionOrNull($group);
    }


    public static function key($group) : I18nKeyInterface
    {
        return static::getInstance()->key($group);
    }

    public static function keyOrNull($group) : ?I18nKeyInterface
    {
        return static::getInstance()->keyOrNull($group);
    }


    public static function getInstance(?I18nTypeInterface $type = null) : I18nTypeInterface
    {
        return static::$instance = $type ?? static::$instance ?? new I18nTypeManager();
    }

    /**
     * @var I18nTypeInterface
     */
    protected static $instance;
}
