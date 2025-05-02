<?php
/**
 * @noinspection PhpUndefinedNamespaceInspection
 * @noinspection PhpUndefinedClassInspection
 */

namespace Gzhegow\I18n;

use Gzhegow\I18n\Pool\I18nPoolInterface;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nAwordInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Pool\I18nPoolItemInterface;
use Gzhegow\I18n\Exception\RuntimeException;
use Gzhegow\I18n\Language\I18nLanguageInterface;
use Gzhegow\I18n\Repository\I18nRepositoryInterface;


interface I18nInterface
{
    public function getRepository() : I18nRepositoryInterface;

    public function getPool() : I18nPoolInterface;


    /**
     * @return string[]
     */
    public function getLangs() : array;

    public function getLangsRegexForRoute(string $regexGroupName = null, string $regexBraces = '/', string $regexFlags = '') : string;


    public function getLang() : string;

    public function getLangDefault() : string;

    public function getLangForUrl(string $lang = null) : ?string;


    public function setLang(string $lang) : I18nInterface;

    public function setLangDefault(string $lang) : I18nInterface;


    /**
     * @return I18nLanguageInterface[]
     */
    public function getLanguages() : array;

    public function getLanguage() : I18nLanguageInterface;

    public function getLanguageDefault() : I18nLanguageInterface;

    public function getLanguageFor(string $lang) : ?I18nLanguageInterface;


    public function getLocale() : string;

    public function getLocaleDefault() : string;

    public function getLocaleFor(string $lang) : ?string;


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


    public function resetUses() : I18nInterface;

    public function useAwords(array $awords, array $groups = null, array $langs = null) : I18nInterface;

    public function useGroups(array $groups, string $lang = null) : I18nInterface;

    public function clearUsesLoaded() : I18nInterface;

    public function loadUses() : I18nInterface;


    /**
     * @return string[]
     */
    public function getGroupsLoaded(array $langs = null) : array;

    /**
     * @return string[]
     */
    public function getLangsLoaded(array $groups = null) : array;


    public function interpolate(?string $phrase, array $placeholders = null) : ?string;


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
        array $placeholders = null,
        array $groups = null, array $langs = null
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
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : array;

    /**
     * @param I18nAwordInterface|string             $aword
     * @param array<string, string>|null            $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     */
    public function phraseOrDefault(
        $aword,
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : string;

    /**
     * @param I18nAwordInterface|string             $aword
     * @param array{0?: string}                     $fallback
     * @param array<string, string>|null            $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @throws RuntimeException
     */
    public function phrase(
        $aword, array $fallback = [],
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : ?string;


    /**
     * @param array<int|float|string>               $numbers
     * @param array<I18nAwordInterface|string>      $awords
     * @param array<string, string>[]|null          $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{0: string, 1: string}[]
     */
    public function choicesOrDefault(
        array $numbers, array $awords,
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : array;

    /**
     * @param array<int|float|string>               $numbers
     * @param array<I18nAwordInterface|string>      $awords
     * @param string[]                              $fallbacks
     * @param array<string, string>[]|null          $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{0: string, 1: string|null}[]
     * @throws RuntimeException
     */
    public function choices(
        array $numbers, array $awords, array $fallbacks = [],
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : array;

    /**
     * @param int|float|string                      $number
     * @param I18nAwordInterface|string             $aword
     * @param array<string, string>|null            $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{0: string, 1: string}
     */
    public function choiceOrDefault(
        $number, $aword,
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : array;

    /**
     * @param int|float|string                      $number
     * @param I18nAwordInterface|string             $aword
     * @param array{0?: string}                     $fallback
     * @param array<string, string>|null            $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{0: string, 1: string|null}
     * @throws RuntimeException
     */
    public function choice(
        $number, $aword, array $fallback = [],
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : array;


    /**
     * @param array<I18nAwordInterface|string>      $awords
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{
     *     0: array{0: int, 1: string, 2?: array}[],
     *     1: I18nPoolItemInterface[]
     * }
     */
    public function get(array $awords, array $groups = null, array $langs = null) : array;

    /**
     * @param array<I18nAwordInterface|string>      $awords
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{
     *     0: array{0: int, 1: string, 2?: array}[],
     *     1: I18nPoolItemInterface[]
     * }
     */
    public function getOrDefault(array $awords, array $groups = null, array $langs = null) : array;
}
