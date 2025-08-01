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


class I18n
{
    const AWORD_PREFIX = '@';

    const WORD_SEPARATOR  = '.';
    const INDEX_SEPARATOR = '|';

    const PLACEHOLDER_BRACES = [ '[:', ':]' ];

    const E_EXCLUDED_GROUP  = 1;
    const E_FORGOTTEN_GROUP = 2;
    const E_MISSING_WORD    = 3;
    const E_WRONG_AWORD     = 4;

    const E_LIST = [
        self::E_EXCLUDED_GROUP  => 'E_EXCLUDED_GROUP',
        self::E_FORGOTTEN_GROUP => 'E_FORGOTTEN_GROUP',
        self::E_MISSING_WORD    => 'E_MISSING_WORD',
        self::E_WRONG_AWORD     => 'E_WRONG_AWORD',
    ];


    private function __construct()
    {
    }


    public static function getConfig() : I18nConfig
    {
        return static::$facade->getConfig();
    }


    public static function getFactory() : I18nFactoryInterface
    {
        return static::$facade->getFactory();
    }


    public static function getPool() : I18nPoolInterface
    {
        return static::$facade->getPool();
    }

    public static function getRepository() : I18nRepositoryInterface
    {
        return static::$facade->getRepository();
    }


    public static function getInterpolator() : I18nInterpolatorInterface
    {
        return static::$facade->getInterpolator();
    }


    public static function getStore() : I18nStore
    {
        return static::$facade->getStore();
    }


    /**
     * @return string[]
     */
    public static function getLangs() : array
    {
        return static::$facade->getLangs();
    }


    public static function getLangsRegex(
        string $stringPrefix = '', string $stringSuffix = '',
        ?string $regexGroupName = null,
        string $regexBraces = '/',
        string $regexFlags = ''
    ) : ?string
    {
        return static::$facade->getLangsRegex(
            $stringPrefix, $stringSuffix,
            $regexGroupName, $regexBraces, $regexFlags
        );
    }


    public static function hasLang(?string $lang) : bool
    {
        return static::$facade->hasLang($lang);
    }

    public static function isLangCurrent(?string $lang) : bool
    {
        return static::$facade->isLangCurrent($lang);
    }

    public static function isLangDefault(?string $lang) : bool
    {
        return static::$facade->isLangDefault($lang);
    }


    public static function getLangCurrent() : string
    {
        return static::$facade->getLangCurrent();
    }

    public static function setLangCurrent(string $lang) : string
    {
        return static::$facade->setLangCurrent($lang);
    }

    /**
     * @return I18nInterface
     */
    public static function setFnOnSetLangCurrent(?\Closure $fnOnSetLangCurrent)
    {
        return static::$facade->setFnOnSetLangCurrent($fnOnSetLangCurrent);
    }


    public static function getLangDefault() : string
    {
        return static::$facade->getLangDefault();
    }

    public static function setLangDefault(string $lang) : string
    {
        return static::$facade->setLangDefault($lang);
    }


    public static function getLangUrlFor(string $lang) : string
    {
        return static::$facade->getLangUrlFor($lang);
    }

    public static function getLangUrlCurrent() : string
    {
        return static::$facade->getLangUrlCurrent();
    }


    /**
     * @return array<string, I18nLanguageInterface>
     */
    public static function getLanguages() : array
    {
        return static::$facade->getLanguages();
    }


    public static function hasLanguage(string $lang, ?I18nLanguageInterface &$language = null) : bool
    {
        return static::$facade->hasLanguage($lang, $language);
    }

    public static function getLanguage(string $lang) : I18nLanguageInterface
    {
        return static::$facade->getLanguage($lang);
    }

    public static function getLanguageCurrent() : I18nLanguageInterface
    {
        return static::$facade->getLanguageCurrent();
    }

    public static function getLanguageDefault() : I18nLanguageInterface
    {
        return static::$facade->getLanguageDefault();
    }


    public static function getLocaleCurrent() : string
    {
        return static::$facade->getLocaleCurrent();
    }

    public static function getLocaleDefault() : string
    {
        return static::$facade->getLocaleDefault();
    }

    public static function getLocaleFor(string $lang) : ?string
    {
        return static::$facade->getLocaleFor($lang);
    }


    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return \Psr\Log\LoggerInterface|null
     */
    public static function setLogger($logger)
    {
        return static::$facade->setLogger($logger);
    }

    /**
     * @param array<int, int> $loggables
     */
    public static function setLoggables(array $loggables) : array
    {
        return static::$facade->setLoggables($loggables);
    }


    /**
     * @return I18nInterface
     */
    public static function resetUses()
    {
        return static::$facade->resetUses();
    }

    /**
     * @return I18nInterface
     */
    public static function resetQueue()
    {
        return static::$facade->resetQueue();
    }

    /**
     * @return I18nInterface
     */
    public static function resetPool()
    {
        return static::$facade->resetPool();
    }

    /**
     * @return I18nInterface
     */
    public static function useAwords(array $awords, ?array $groups = null, ?array $langs = null)
    {
        return static::$facade->useAwords($awords, $groups, $langs);
    }

    /**
     * @return I18nInterface
     */
    public static function useGroups(array $groups, ?string $lang = null)
    {
        return static::$facade->useGroups($groups, $lang);
    }


    /**
     * @return I18nInterface
     */
    public static function loadUses()
    {
        return static::$facade->loadUses();
    }


    /**
     * @return string[]
     */
    public static function getGroupsLoaded(?array $langs = null) : array
    {
        return static::$facade->getGroupsLoaded($langs);
    }

    /**
     * @return string[]
     */
    public static function getLangsLoaded(?array $groups = null) : array
    {
        return static::$facade->getLangsLoaded($groups);
    }


    public static function interpolate(?string $phrase, ?array $placeholders = null) : ?string
    {
        return static::$facade->interpolate($phrase, $placeholders);
    }


    /**
     * @param array<I18nAwordInterface|string>      $awords
     * @param array<string, string>[]|null          $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return string[]
     */
    public static function phrasesOrDefault(
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
    public static function phrases(
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
    public static function phraseOrDefault(
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
    public static function phrase(
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
    public static function choicesOrDefault(
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
    public static function choices(
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
    public static function choiceOrDefault(
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
    public static function choice(
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
    public static function get(array $awords, ?array $groups = null, ?array $langs = null) : array
    {
        return static::$facade->get($awords, $groups, $langs);
    }

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
    public static function getOrDefault(array $awords, ?array $groups = null, ?array $langs = null) : array
    {
        return static::$facade->getOrDefault($awords, $groups, $langs);
    }


    public static function setFacade(?I18nInterface $facade) : ?I18nInterface
    {
        $last = static::$facade;

        static::$facade = $facade;

        return $last;
    }

    /**
     * @var I18nInterface
     */
    protected static $facade;
}
