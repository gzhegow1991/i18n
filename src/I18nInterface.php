<?php

/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\I18n;

use Gzhegow\I18n\Store\I18nStore;
use Gzhegow\I18n\Config\I18nConfig;
use Gzhegow\I18n\Pool\I18nPoolInterface;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nAwordInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Exception\RuntimeException;
use Gzhegow\I18n\Language\I18nLanguageInterface;
use Gzhegow\I18n\Repository\I18nRepositoryInterface;
use Gzhegow\I18n\Pool\PoolItem\I18nPoolItemInterface;
use Gzhegow\I18n\Interpolator\I18nInterpolatorInterface;


interface I18nInterface
{
    public function getConfig() : I18nConfig;


    public function getFactory() : I18nFactoryInterface;


    public function getPool() : I18nPoolInterface;

    public function getRepository() : I18nRepositoryInterface;


    public function getInterpolator() : I18nInterpolatorInterface;


    public function getStore() : I18nStore;


    /**
     * @return string[]
     */
    public function getLangs() : array;


    public function getLangsRegex(
        string $stringPrefix = '', string $stringSuffix = '',
        ?string $regexGroupName = null, string $regexBraces = '/', string $regexFlags = ''
    ) : ?string;


    public function hasLang(string $lang) : bool;

    public function isLangCurrent(string $lang) : bool;

    public function isLangDefault(string $lang) : bool;


    public function getLangCurrent() : string;

    public function setLangCurrent(string $lang) : string;

    /**
     * @return static
     */
    public function setFnOnSetLangCurrent(?\Closure $fnOnSetLangCurrent);


    public function getLangDefault() : string;

    public function setLangDefault(string $lang) : string;


    /**
     * @return array<string, I18nLanguageInterface>
     */
    public function getLanguages() : array;

    public function hasLanguage(string $lang, ?I18nLanguageInterface &$language = null) : bool;

    public function getLanguage(string $lang) : I18nLanguageInterface;

    public function getLanguageCurrent() : I18nLanguageInterface;

    public function getLanguageDefault() : I18nLanguageInterface;


    public function getLocaleFor(string $lang) : string;

    public function getLocaleCurrent() : string;

    public function getLocaleDefault() : string;


    public function getLangUrlFor(string $lang) : string;

    public function getLangUrlCurrent() : string;


    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return \Psr\Log\LoggerInterface|null
     */
    public function setLogger($logger);

    /**
     * @param array<int, int> $loggables
     */
    public function setLoggables(array $loggables) : array;


    /**
     * @return static
     */
    public function resetUses();

    /**
     * @return static
     */
    public function resetQueue();

    /**
     * @return static
     */
    public function resetPool();

    /**
     * @return static
     */
    public function useAwords(array $awords, ?array $groups = null, ?array $langs = null);

    /**
     * @return static
     */
    public function useGroups(array $groups, ?string $lang = null);


    /**
     * @return static
     */
    public function loadUses();


    /**
     * @return string[]
     */
    public function getGroupsLoaded(?array $langs = null) : array;

    /**
     * @return string[]
     */
    public function getLangsLoaded(?array $groups = null) : array;


    public function interpolate(?string $phrase, ?array $placeholders = null) : ?string;


    /**
     * @param array<I18nAwordInterface|string>      $awords
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{
     *     0: array{ 0: int, 1: string, 2?: array }[],
     *     1: I18nPoolItemInterface[]
     * }
     */
    public function getOrDefault(array $awords, ?array $groups = null, ?array $langs = null) : array;

    /**
     * @param array<I18nAwordInterface|string>      $awords
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{
     *     0: array{ 0: int, 1: string, 2?: array }[],
     *     1: I18nPoolItemInterface[]
     * }
     */
    public function get(array $awords, ?array $groups = null, ?array $langs = null) : array;


    /**
     * @param array<I18nAwordInterface|string>      $awords
     * @param array<string, string>[]|null          $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return string[]
     */
    public function phrasesOrDefault(
        array $awords,
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array;

    /**
     * @param array<I18nAwordInterface|string>      $awords
     * @param string[]                              $fallbacks
     * @param array<string, string>[]|null          $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return (string|null)[]
     * @throws RuntimeException
     */
    public function phrases(
        array $awords, array $fallbacks = [],
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array;


    /**
     * @param I18nAwordInterface|string             $aword
     * @param array<string, string>|null            $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     */
    public function phraseOrDefault(
        $aword,
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : string;

    /**
     * @param I18nAwordInterface|string             $aword
     * @param array{ 0?: string }                   $fallback
     * @param array<string, string>|null            $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @throws RuntimeException
     */
    public function phrase(
        $aword, array $fallback = [],
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : ?string;


    /**
     * @param array<int|float|string>               $numbers
     * @param array<I18nAwordInterface|string>      $awords
     * @param array<string, string>[]|null          $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{ 0: string, 1: string }[]
     */
    public function choicesOrDefault(
        array $numbers, array $awords,
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array;

    /**
     * @param array<int|float|string>               $numbers
     * @param array<I18nAwordInterface|string>      $awords
     * @param string[]                              $fallbacks
     * @param array<string, string>[]|null          $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{ 0: string, 1: string|null }[]
     * @throws RuntimeException
     */
    public function choices(
        array $numbers, array $awords, array $fallbacks = [],
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array;


    /**
     * @param int|float|string                      $number
     * @param I18nAwordInterface|string             $aword
     * @param array<string, string>|null            $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{ 0: string, 1: string }
     */
    public function choiceOrDefault(
        $number, $aword,
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array;

    /**
     * @param int|float|string                      $number
     * @param I18nAwordInterface|string             $aword
     * @param array{0?: string}                     $fallback
     * @param array<string, string>|null            $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{ 0: string, 1: string|null }
     * @throws RuntimeException
     */
    public function choice(
        $number, $aword, array $fallback = [],
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array;
}
