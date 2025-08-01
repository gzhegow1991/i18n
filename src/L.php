<?php

namespace Gzhegow\I18n;

use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nAwordInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Exception\RuntimeException;


class L extends I18n
{
    /**
     * @param array<I18nAwordInterface|string>      $awords
     * @param array<string, string>[]|null          $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return string[]
     */
    public static function ppd(
        array $awords,
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        return static::$facade->phrasesOrDefault(
            $awords,
            $placeholders,
            $groups, $langs
        );
    }

    /**
     * @param array<I18nAwordInterface|string>      $awords
     * @param string[]|null                         $fallbacks
     * @param array<string, string>[]|null          $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return (string|null)[]
     * @throws RuntimeException
     */
    public static function pp(
        array $awords, array $fallbacks = [],
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        return static::$facade->phrases(
            $awords, $fallbacks,
            $placeholders,
            $groups, $langs
        );
    }

    /**
     * @param I18nAwordInterface|string             $aword
     * @param array<string, string>|null            $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     */
    public static function pd(
        $aword,
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : string
    {
        return static::$facade->phraseOrDefault(
            $aword,
            $placeholders,
            $groups, $langs
        );
    }

    /**
     * @param I18nAwordInterface|string             $aword
     * @param array{0?: string}|null                $fallback
     * @param array<string, string>|null            $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @throws RuntimeException
     */
    public static function p(
        $aword, array $fallback = [],
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : ?string
    {
        return static::$facade->phrase(
            $aword, $fallback,
            $placeholders,
            $groups, $langs
        );
    }


    /**
     * @param array<int|float|string>               $numbers
     * @param array<I18nAwordInterface|string>      $awords
     * @param array<string, string>[]|null          $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{0: string, 1: string}[]
     */
    public static function ccd(
        array $numbers, array $awords,
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        return static::$facade->choicesOrDefault(
            $numbers, $awords,
            $placeholders,
            $groups, $langs
        );
    }

    /**
     * @param array<int|float|string>               $numbers
     * @param array<I18nAwordInterface|string>      $awords
     * @param string[]|null                         $fallbacks
     * @param array<string, string>[]|null          $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{0: string, 1: string|null}[]
     * @throws RuntimeException
     */
    public static function cc(
        array $numbers, array $awords, array $fallbacks = [],
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        return static::$facade->choices(
            $numbers, $awords, $fallbacks,
            $placeholders,
            $groups, $langs
        );
    }

    /**
     * @param int|float|string                      $number
     * @param I18nAwordInterface|string             $aword
     * @param array<string, string>|null            $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{0: string, 1: string}
     */
    public static function cd(
        $number, $aword,
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        return static::$facade->choiceOrDefault(
            $number, $aword,
            $placeholders,
            $groups, $langs
        );
    }

    /**
     * @param int|float|string                      $number
     * @param I18nAwordInterface|string             $aword
     * @param array{0?: string}|null                $fallback
     * @param array<string, string>|null            $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{0: string, 1: string|null}
     * @throws RuntimeException
     */
    public static function c(
        $number, $aword, array $fallback = [],
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        return static::$facade->choice(
            $number, $aword, $fallback,
            $placeholders,
            $groups, $langs
        );
    }
}
