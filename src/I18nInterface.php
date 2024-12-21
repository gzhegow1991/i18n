<?php
/**
 * @noinspection PhpUndefinedNamespaceInspection
 * @noinspection PhpUndefinedClassInspection
 */

namespace Gzhegow\I18n;

use Gzhegow\I18n\Struct\LangInterface;
use Gzhegow\I18n\Struct\GroupInterface;
use Gzhegow\I18n\Struct\AwordInterface;
use Gzhegow\I18n\Pool\I18nPoolInterface;
use Gzhegow\I18n\Struct\LanguageInterface;
use Gzhegow\I18n\Pool\I18nPoolItemInterface;
use Gzhegow\I18n\Exception\RuntimeException;
use Gzhegow\I18n\Repository\I18nRepositoryInterface;


interface I18nInterface
{
    const E_WRONG_AWORD     = 1 << 0;
    const E_FORGOTTEN_GROUP = 1 << 1;
    const E_MISSING_WORD    = 1 << 2;

    const E_LIST = [
        self::E_WRONG_AWORD     => true,
        self::E_FORGOTTEN_GROUP => true,
        self::E_MISSING_WORD    => true,
    ];


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

    /**
     * @return static
     */
    public function setLang(string $lang);

    /**
     * @return static
     */
    public function setLangDefault(string $lang);


    /**
     * @return LanguageInterface[]
     */
    public function getLanguages() : array;

    public function getLanguage() : LanguageInterface;

    public function getLanguageDefault() : LanguageInterface;

    public function getLanguageFor(string $lang) : ?LanguageInterface;


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


    /**
     * @return static
     */
    public function resetUses();

    /**
     * @return static
     */
    public function useAwords(array $awords, array $groups = null, array $langs = null);

    /**
     * @return static
     */
    public function useGroups(array $groups, string $lang = null);

    /**
     * @return static
     */
    public function clearUsesLoaded();

    /**
     * @return static
     */
    public function loadUses();

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
     * @param array<AwordInterface|string>      $awords
     * @param array<string, string>[]|null      $placeholders
     * @param array<GroupInterface|string>|null $groups
     * @param array<LangInterface|string>|null  $langs
     *
     * @return string[]
     */
    public function phrasesOrDefault(
        array $awords,
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : array;

    /**
     * @param array<AwordInterface|string>      $awords
     * @param string[]|null                     $fallbacks
     * @param array<string, string>[]|null      $placeholders
     * @param array<GroupInterface|string>|null $groups
     * @param array<LangInterface|string>|null  $langs
     *
     * @return (string|null)[]
     * @throws RuntimeException
     */
    public function phrases(
        array $awords, array $fallbacks = null,
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : array;

    /**
     * @param AwordInterface|string             $aword
     * @param array<string, string>|null        $placeholders
     * @param array<GroupInterface|string>|null $groups
     * @param array<LangInterface|string>|null  $langs
     */
    public function phraseOrDefault(
        $aword,
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : string;

    /**
     * @param AwordInterface|string             $aword
     * @param array{0?: string}|null            $fallback
     * @param array<string, string>|null        $placeholders
     * @param array<GroupInterface|string>|null $groups
     * @param array<LangInterface|string>|null  $langs
     *
     * @throws RuntimeException
     */
    public function phrase(
        $aword, array $fallback = null,
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : ?string;


    /**
     * @param array<int|float|string>           $numbers
     * @param array<AwordInterface|string>      $awords
     * @param array<string, string>[]|null      $placeholders
     * @param array<GroupInterface|string>|null $groups
     * @param array<LangInterface|string>|null  $langs
     *
     * @return array{0: string, 1: string}[]
     */
    public function choicesOrDefault(
        array $numbers, array $awords,
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : array;

    /**
     * @param array<int|float|string>           $numbers
     * @param array<AwordInterface|string>      $awords
     * @param string[]|null                     $fallbacks
     * @param array<string, string>[]|null      $placeholders
     * @param array<GroupInterface|string>|null $groups
     * @param array<LangInterface|string>|null  $langs
     *
     * @return array{0: string, 1: string|null}[]
     * @throws RuntimeException
     */
    public function choices(
        array $numbers, array $awords, array $fallbacks = null,
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : array;

    /**
     * @param int|float|string                  $number
     * @param AwordInterface|string             $aword
     * @param array<string, string>|null        $placeholders
     * @param array<GroupInterface|string>|null $groups
     * @param array<LangInterface|string>|null  $langs
     *
     * @return array{0: string, 1: string}
     */
    public function choiceOrDefault(
        $number, $aword,
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : array;

    /**
     * @param int|float|string                  $number
     * @param AwordInterface|string             $aword
     * @param array{0?: string}|null            $fallback
     * @param array<string, string>|null        $placeholders
     * @param array<GroupInterface|string>|null $groups
     * @param array<LangInterface|string>|null  $langs
     *
     * @return array{0: string, 1: string|null}
     * @throws RuntimeException
     */
    public function choice(
        $number, $aword, array $fallback = null,
        array $placeholders = null,
        array $groups = null, array $langs = null
    ) : array;


    /**
     * @param array<AwordInterface|string>      $awords
     * @param array<GroupInterface|string>|null $groups
     * @param array<LangInterface|string>|null  $langs
     *
     * @return array{
     *     0: array{0: int, 1: string, 2?: array}[],
     *     1: I18nPoolItemInterface[]
     * }
     */
    public function get(array $awords, array $groups = null, array $langs = null) : array;

    /**
     * @param array<AwordInterface|string>      $awords
     * @param array<GroupInterface|string>|null $groups
     * @param array<LangInterface|string>|null  $langs
     *
     * @return array{
     *     0: array{0: int, 1: string, 2?: array}[],
     *     1: I18nPoolItemInterface[]
     * }
     */
    public function getOrDefault(array $awords, array $groups = null, array $langs = null) : array;
}
