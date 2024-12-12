<?php

namespace Gzhegow\I18n\Type;

use Gzhegow\I18n\Struct\LangInterface;
use Gzhegow\I18n\Struct\WordInterface;
use Gzhegow\I18n\Struct\GroupInterface;
use Gzhegow\I18n\Struct\AwordInterface;
use Gzhegow\I18n\Pool\PoolItemInterface;
use Gzhegow\I18n\Struct\LanguageInterface;
use Gzhegow\I18n\Repository\File\Struct\FileSourceInterface;


class Type
{
    public static function parseLanguage($value) : ?LanguageInterface
    {
        return static::$instance->parseLanguage($value);
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


    public static function parsePoolItem($value) : ?PoolItemInterface
    {
        return static::$instance->parsePoolItem($value);
    }

    /**
     * @return PoolItemInterface[]|null
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

    public static function thePoolItem($value) : PoolItemInterface
    {
        return static::parsePoolItem($value);
    }

    /**
     * @return PoolItemInterface[]
     */
    public static function thePoolItemList($values) : array
    {
        return static::parsePoolItemList($values);
    }


    public static function parseLang($value) : ?LangInterface
    {
        return static::$instance->parseLang($value);
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
        return static::$instance->parseGroup($group);
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
        return static::$instance->parseAword($value);
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
        return static::$instance->parseWord($value);
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
        return static::$instance->parseFileSource($value);
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


    public static function getInstance() : TypeManagerInterface
    {
        return static::$instance;
    }

    public static function setInstance(TypeManagerInterface $typeManager) : ?TypeManagerInterface
    {
        $current = static::$instance;

        static::$instance = $typeManager;

        return $current;
    }

    /**
     * @var TypeManagerInterface
     */
    protected static $instance;
}
