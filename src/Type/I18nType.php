<?php

namespace Gzhegow\I18n\Type;

use Gzhegow\I18n\Struct\LangInterface;
use Gzhegow\I18n\Struct\WordInterface;
use Gzhegow\I18n\Struct\GroupInterface;
use Gzhegow\I18n\Struct\AwordInterface;
use Gzhegow\I18n\Struct\LanguageInterface;
use Gzhegow\I18n\Pool\I18nPoolItemInterface;
use Gzhegow\I18n\Repository\File\Struct\FileSourceInterface;


class I18nType
{
    public static function parseLanguage($value) : ?LanguageInterface
    {
        return static::$manager->parseLanguage($value);
    }

    /**
     * @return LanguageInterface[]|null
     */
    public static function parseLanguageList($values) : ?array
    {
        if (! is_iterable($values)) {
            return null;
        }

        $list = [];

        foreach ( $values as $value ) {
            if (null === ($item = static::parseLanguage($value))) {
                return null;
            }

            $list[] = $item;
        }

        return $list;
    }

    public static function theLanguage($value) : LanguageInterface
    {
        return static::parseLanguage($value);
    }

    /**
     * @return LanguageInterface[]
     */
    public static function theLanguageList($values) : array
    {
        return static::parseLanguageList($values);
    }


    public static function parsePoolItem($value) : ?I18nPoolItemInterface
    {
        return static::$manager->parsePoolItem($value);
    }

    /**
     * @return I18nPoolItemInterface[]|null
     */
    public static function parsePoolItemList($values) : ?array
    {
        if (! is_iterable($values)) {
            return null;
        }

        $list = [];

        foreach ( $values as $value ) {
            if (null === ($item = static::parsePoolItem($value))) {
                return null;
            }

            $list[] = $item;
        }

        return $list;
    }

    public static function thePoolItem($value) : I18nPoolItemInterface
    {
        return static::parsePoolItem($value);
    }

    /**
     * @return I18nPoolItemInterface[]
     */
    public static function thePoolItemList($values) : array
    {
        return static::parsePoolItemList($values);
    }


    public static function parseLang($value) : ?LangInterface
    {
        return static::$manager->parseLang($value);
    }

    /**
     * @return LangInterface[]|null
     */
    public static function parseLangList($values) : ?array
    {
        if (! is_iterable($values)) {
            return null;
        }

        $list = [];

        foreach ( $values as $value ) {
            if (null === ($item = static::parseLang($value))) {
                return null;
            }

            $list[] = $item;
        }

        return $list;
    }

    public static function theLang($lang) : LangInterface
    {
        return static::parseLang($lang);
    }

    /**
     * @return LangInterface[]
     */
    public static function theLangList($values) : array
    {
        return static::parseLangList($values);
    }


    public static function parseGroup($group) : ?GroupInterface
    {
        return static::$manager->parseGroup($group);
    }

    /**
     * @return GroupInterface[]|null
     */
    public static function parseGroupList($values) : ?array
    {
        if (! is_iterable($values)) {
            return null;
        }

        $list = [];

        foreach ( $values as $value ) {
            if (null === ($item = static::parseGroup($value))) {
                return null;
            }

            $list[] = $item;
        }

        return $list;
    }

    public static function theGroup($group) : GroupInterface
    {
        return static::parseGroup($group);
    }

    /**
     * @return GroupInterface[]
     */
    public static function theGroupList($values) : array
    {
        return static::parseGroupList($values);
    }


    public static function parseAword($value) : ?AwordInterface
    {
        return static::$manager->parseAword($value);
    }

    /**
     * @return AwordInterface[]|null
     */
    public static function parseAwordList($values) : ?array
    {
        if (! is_iterable($values)) {
            return null;
        }

        $list = [];

        foreach ( $values as $value ) {
            if (null === ($item = static::parseAword($value))) {
                return null;
            }

            $list[] = $item;
        }

        return $list;
    }

    public static function theAword($value) : AwordInterface
    {
        return static::parseAword($value);
    }

    /**
     * @return AwordInterface[]
     */
    public static function theAwordList($values) : array
    {
        return static::parseAwordList($values);
    }


    public static function parseWord($value) : ?WordInterface
    {
        return static::$manager->parseWord($value);
    }

    /**
     * @return WordInterface[]|null
     */
    public static function parseWordList($values) : ?array
    {
        if (! is_iterable($values)) {
            return null;
        }

        $list = [];

        foreach ( $values as $value ) {
            if (null === ($item = static::parseWord($value))) {
                return null;
            }

            $list[] = $item;
        }

        return $list;
    }

    public static function theWord($value) : WordInterface
    {
        return static::parseWord($value);
    }

    /**
     * @return WordInterface[]
     */
    public static function theWordList($values) : array
    {
        return static::parseWordList($values);
    }


    public static function parseFileSource($value) : ?FileSourceInterface
    {
        return static::$manager->parseFileSource($value);
    }

    /**
     * @return FileSourceInterface[]|null
     */
    public static function parseFileSourceList($values) : ?array
    {
        if (! is_iterable($values)) {
            return null;
        }

        $list = [];

        foreach ( $values as $value ) {
            if (null === ($item = static::parseFileSource($value))) {
                return null;
            }

            $list[] = $item;
        }

        return $list;
    }

    public static function theFileSource($value) : FileSourceInterface
    {
        return static::parseFileSource($value);
    }

    /**
     * @return FileSourceInterface[]
     */
    public static function theFileSourceList($values) : array
    {
        return static::parseFileSourceList($values);
    }


    public static function setInstance(I18nTypeManagerInterface $typeManager) : ?I18nTypeManagerInterface
    {
        $last = static::$manager;

        static::$manager = $typeManager;

        return $last;
    }

    /**
     * @var I18nTypeManagerInterface
     */
    protected static $manager;
}
