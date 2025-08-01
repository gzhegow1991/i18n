<?php

/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\I18n;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\I18n\Store\I18nStore;
use Gzhegow\I18n\Config\I18nConfig;
use Gzhegow\I18n\Pool\I18nPoolInterface;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Exception\LogicException;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Struct\I18nAwordInterface;
use Gzhegow\I18n\Exception\RuntimeException;
use Gzhegow\I18n\Language\I18nLanguageInterface;
use Gzhegow\I18n\Repository\I18nRepositoryInterface;
use Gzhegow\I18n\Pool\PoolItem\I18nPoolItemInterface;
use Gzhegow\I18n\PoolManager\I18nPoolManagerInterface;
use Gzhegow\I18n\Interpolator\I18nInterpolatorInterface;


class I18nFacade implements I18nInterface
{
    /**
     * @var I18nConfig
     */
    protected $config;

    /**
     * @var I18nFactoryInterface
     */
    protected $factory;

    /**
     * @var I18nPoolManagerInterface
     */
    protected $poolManager;
    /**
     * @var I18nInterpolatorInterface
     */
    protected $interpolator;

    /**
     * @var I18nStore
     */
    protected $store;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var array<int, int>
     */
    protected $loggables = [
        I18n::E_EXCLUDED_GROUP  => 0,
        I18n::E_FORGOTTEN_GROUP => 0,
        I18n::E_MISSING_WORD    => 0,
        I18n::E_WRONG_AWORD     => 0,
    ];

    /**
     * @var \Closure|null
     */
    protected $fnOnSetLangCurrent;
    /**
     * @var bool
     */
    protected $lockFnOnSetLangCurrent = false;


    public function __construct(
        I18nFactoryInterface $factory,
        //
        I18nPoolManagerInterface $poolManager,
        I18nInterpolatorInterface $interpolator,
        //
        I18nConfig $config
    )
    {
        $this->factory = $factory;

        $this->poolManager = $poolManager;
        $this->interpolator = $interpolator;

        $this->store = $this->factory->newStore();

        $this->config = $config;
        $this->config->validate();

        $this->store->isDebug = $this->config->isDebug;

        $this->initialize();
    }

    /**
     * @return static
     */
    protected function initialize()
    {
        $this->poolManager->initialize($this);

        $languages = $this->config->languages ?? [];
        $choices = $this->config->choices ?? [];
        $phpLocales = $this->config->phpLocales ?? [];

        $langCurrent = $this->config->langCurrent ?? null;
        $langDefault = $this->config->langDefault ?? null;

        $logger = $this->config->logger ?? null;

        $loggables = [];
        $loggables[ I18n::E_EXCLUDED_GROUP ] = $this->config->loggables[ I18n::E_EXCLUDED_GROUP ] ?? null;
        $loggables[ I18n::E_FORGOTTEN_GROUP ] = $this->config->loggables[ I18n::E_FORGOTTEN_GROUP ] ?? null;
        $loggables[ I18n::E_MISSING_WORD ] = $this->config->loggables[ I18n::E_MISSING_WORD ] ?? null;
        $loggables[ I18n::E_WRONG_AWORD ] = $this->config->loggables[ I18n::E_WRONG_AWORD ] ?? null;
        $loggablesFiltered = array_filter($loggables);

        foreach ( $languages as $key => $array ) {
            $languageArray = [
                'lang'         => $key,
                'locale'       => $array[ 0 ],
                'script'       => $array[ 1 ],
                'titleEnglish' => $array[ 2 ],
                'titleNative'  => $array[ 3 ],
            ];

            $languageObject = I18nType::language($languageArray);

            $langString = $languageObject->getLang();

            $languageObject->setPhpLocales($phpLocales[ $langString ]);
            $languageObject->setChoice($choices[ $langString ]);

            $this->store->languages[ $langString ] = $languageObject;
        }

        if (null !== $langCurrent) {
            $this->setLangCurrent($langCurrent);
        }

        if (null !== $langDefault) {
            $this->setLangDefault($langDefault);
        }

        if (null !== $logger) {
            $this->setLogger($logger);
        }

        if ([] !== $loggablesFiltered) {
            $this->setLoggables($loggables);
        }

        return $this;
    }


    public function getConfig() : I18nConfig
    {
        return $this->config;
    }


    public function getFactory() : I18nFactoryInterface
    {
        return $this->factory;
    }


    public function getPool() : I18nPoolInterface
    {
        return $this->poolManager->getPool();
    }

    public function getRepository() : I18nRepositoryInterface
    {
        return $this->poolManager->getRepository();
    }


    public function getInterpolator() : I18nInterpolatorInterface
    {
        return $this->interpolator;
    }


    public function getStore() : I18nStore
    {
        return $this->store;
    }


    /**
     * @return string[]
     */
    public function getLangs() : array
    {
        return array_keys($this->store->languages);
    }


    public function getLangsRegex(
        string $stringPrefix = '', string $stringSuffix = '',
        ?string $regexGroupName = null, string $regexBraces = '/', string $regexFlags = ''
    ) : ?string
    {
        $withGroupName = false;
        $withBraces = $regexBraces !== '';
        $withFlags = $regexFlags !== '';

        if ($withFlags && ! $withBraces) {
            throw new LogicException(
                'You have to pass `braces` argument to use flags'
            );
        }

        if (null !== $regexGroupName) {
            if ('' === $regexGroupName) {
                throw new LogicException(
                    'The `groupName` should be non-empty string'
                );
            }

            $withGroupName = true;
        }

        $regex = [];
        foreach ( $this->store->languages as $lang => $language ) {
            $regex[] = preg_quote($lang, $regexBraces[ 0 ] ?? '/');
        }

        if ([] === $regex) {
            return null;
        }

        $regex = implode('|', $regex);

        $regexPrefix = '';
        $regexSuffix = '';
        if ('' !== $stringPrefix) {
            $regexPrefix = preg_quote($stringPrefix, $regexBraces[ 0 ] ?? '/');
        }
        if ('' !== $stringSuffix) {
            $regexSuffix = preg_quote($stringSuffix, $regexBraces[ 0 ] ?? '/');
        }

        if ($withGroupName) {
            $regex = "(?:{$regexPrefix}(?<{$regexGroupName}>{$regex}){$regexSuffix})";

        } elseif ($withBraces || $withFlags) {
            $regex = "(?:{$regexPrefix}({$regex}){$regexSuffix})";
        }

        if ($withBraces) {
            $braceLeft = $regexBraces[ 0 ] ?? '';
            $braceRight = $regexBraces[ 1 ] ?? $braceLeft;

            $regex = $braceLeft . $regex . $braceRight;
        }

        if ($withFlags) {
            $regex .= $regexFlags;
        }

        return $regex;
    }


    public function hasLang(string $lang) : bool
    {
        return isset($this->store->languages[ $lang ]);
    }

    public function isLangCurrent(string $lang) : bool
    {
        return $this->store->langCurrent === $lang;
    }

    public function isLangDefault(string $lang) : bool
    {
        return $this->store->langDefault === $lang;
    }


    public function getLangCurrent() : string
    {
        return $this->store->langCurrent;
    }

    public function setLangCurrent(string $lang) : string
    {
        if ($this->lockFnOnSetLangCurrent) {
            throw new RuntimeException(
                [ 'Unable to call ' . __FUNCTION__ . ' if `lockFnOnSetLangCurrent` is TRUE' ]
            );
        }

        $last = $this->store->langCurrent;

        if ($lang === $this->store->langCurrent) {
            return $lang;
        }

        $language = $this->getLanguage($lang);

        $languageLang = $language->getLang();
        $languagePhpLocales = $language->hasPhpLocales();

        if ([] !== $languagePhpLocales) {
            foreach ( $languagePhpLocales as $category => $locales ) {
                $status = setlocale($category, $locales);

                if ($status === false) {
                    $map = [
                        LC_COLLATE  => 'LC_COLLATE',
                        LC_CTYPE    => 'LC_CTYPE',
                        LC_MONETARY => 'LC_MONETARY',
                        LC_NUMERIC  => 'LC_NUMERIC',
                        LC_TIME     => 'LC_TIME',
                    ];

                    if (defined('LC_MESSAGES')) {
                        $map[ LC_MESSAGES ] = 'LC_MESSAGES';
                    }

                    throw new LogicException(
                        [
                            'Missing locales in your OS: ' . $map[ $category ],
                            $locales,
                        ]
                    );
                }
            }
        }

        $this->store->langCurrent = $language->getLang();

        if (null !== $this->fnOnSetLangCurrent) {
            $this->lockFnOnSetLangCurrent = true;

            $fn = $this->fnOnSetLangCurrent;

            $fn($languageLang, $this);

            $this->lockFnOnSetLangCurrent = false;
        }

        return $last;
    }

    /**
     * @return static
     */
    public function setFnOnSetLangCurrent(?\Closure $fnOnSetLangCurrent)
    {
        $this->fnOnSetLangCurrent = $fnOnSetLangCurrent;

        return $this;
    }


    public function getLangDefault() : string
    {
        return $this->store->langDefault;
    }

    public function setLangDefault(string $lang) : string
    {
        $last = $this->store->langDefault;

        $language = $this->getLanguage($lang);

        $this->store->langDefault = $language->getLang();

        return $last;
    }


    /**
     * @return array<string, I18nLanguageInterface>
     */
    public function getLanguages() : array
    {
        return $this->store->languages;
    }


    public function hasLanguage(string $lang, ?I18nLanguageInterface &$language = null) : bool
    {
        $language = null;

        if (isset($this->store->languages[ $lang ])) {
            $language = $this->store->languages[ $lang ];

            return true;
        }

        return false;
    }

    public function getLanguage(string $lang) : I18nLanguageInterface
    {
        return $this->store->languages[ $lang ];
    }

    public function getLanguageCurrent() : I18nLanguageInterface
    {
        return $this->getLanguage($this->store->langCurrent);
    }

    public function getLanguageDefault() : I18nLanguageInterface
    {
        return $this->getLanguage($this->store->langDefault);
    }


    public function getLocaleFor(string $lang) : string
    {
        $language = $this->getLanguage($lang);

        $locale = $language->getLocale();

        return $locale;
    }

    public function getLocaleCurrent() : string
    {
        return $this->getLocaleFor($this->store->langCurrent);
    }

    public function getLocaleDefault() : string
    {
        return $this->getLocaleFor($this->store->langDefault);
    }


    public function getLangUrlFor(string $lang) : string
    {
        $langUrl = ($lang === $this->store->langDefault)
            ? ''
            : $lang;

        return $langUrl;
    }

    public function getLangUrlCurrent() : string
    {
        return $this->getLangUrlFor($this->store->langCurrent);
    }


    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return \Psr\Log\LoggerInterface|null
     */
    public function setLogger($logger)
    {
        $last = $this->logger;

        if (null !== $logger) {
            if (! is_a($logger, $class = '\Psr\Log\LoggerInterface')) {
                throw new LogicException(
                    'The `logger` should be instance of: ' . $class
                );
            }
        }

        $this->logger = $logger;

        return $last;
    }

    /**
     * @param array<int, int>|null $loggables
     */
    public function setLoggables(?array $loggables = null) : array
    {
        $theType = Lib::type();

        $last = $this->loggables;

        if (null !== $loggables) {
            $eExcludedGroup = $loggables[ I18n::E_EXCLUDED_GROUP ] ?? null;
            $eForgottenGroup = $loggables[ I18n::E_FORGOTTEN_GROUP ] ?? null;
            $eMissingWord = $loggables[ I18n::E_MISSING_WORD ] ?? null;
            $eWrongAword = $loggables[ I18n::E_WRONG_AWORD ] ?? null;

            $eExcludedGroup = $theType->int_non_negative($eExcludedGroup)->orNull();
            $eForgottenGroup = $theType->int_non_negative($eForgottenGroup)->orNull();
            $eMissingWord = $theType->int_non_negative($eMissingWord)->orNull();
            $eWrongAword = $theType->int_non_negative($eWrongAword)->orNull();

            $this->loggables = [];
            $this->loggables[ I18n::E_EXCLUDED_GROUP ] = $eExcludedGroup ?? 0;
            $this->loggables[ I18n::E_FORGOTTEN_GROUP ] = $eForgottenGroup ?? 0;
            $this->loggables[ I18n::E_MISSING_WORD ] = $eMissingWord ?? 0;
            $this->loggables[ I18n::E_WRONG_AWORD ] = $eWrongAword ?? 0;
        }

        return $last;
    }


    /**
     * @return static
     */
    public function resetUses()
    {
        $this->poolManager->resetQueue();
        $this->poolManager->resetPool();

        return $this;
    }

    /**
     * @return static
     */
    public function resetQueue()
    {
        $this->poolManager->resetQueue();

        return $this;
    }

    /**
     * @return static
     */
    public function resetPool()
    {
        $this->poolManager->resetPool();

        return $this;
    }

    /**
     * @return static
     */
    public function useAwords(array $awords, ?array $groups = null, ?array $langs = null)
    {
        $this->poolManager->useAwords($awords, $groups, $langs);

        return $this;
    }

    /**
     * @param array       $groups
     * @param string|null $lang
     *
     * @return static
     */
    public function useGroups(array $groups, ?string $lang = null)
    {
        $this->poolManager->useGroups($groups, $lang);

        return $this;
    }


    /**
     * @return static
     */
    public function loadUses()
    {
        $this->poolManager->loadUses();

        return $this;
    }


    /**
     * @param (I18nLangInterface|string)[] $langs
     *
     * @return string[]
     */
    public function getGroupsLoaded(?array $langs = null) : array
    {
        return $this->poolManager->getGroupsLoaded($langs);
    }

    /**
     * @param (I18nGroupInterface|string)[] $groups
     *
     * @return string[]
     */
    public function getLangsLoaded(?array $groups = null) : array
    {
        return $this->poolManager->getLangsLoaded($groups);
    }


    public function interpolate(?string $phrase, ?array $placeholders = null) : ?string
    {
        return $this->interpolator->interpolate($phrase, $placeholders);
    }


    /**
     * @param array<I18nAwordInterface|string>                  $awords
     * @param array<I18nGroupInterface|string>|null             $groups
     * @param array<I18nLangInterface|string>|null              $langs
     * @param array{ 0?: array{ 0: int, 1: string, 2: array } } $refs
     *
     * @return array<int, array<string, I18nPoolItemInterface>>
     */
    public function get(array $awords, ?array $groups = null, ?array $langs = null, array $refs = []) : array
    {
        if ([] === $awords) {
            return [];
        }

        $withErrors = array_key_exists(0, $refs);
        if ($withErrors) {
            $refErrors =& $refs[ 0 ];
        }
        $refErrors = [];

        $thePool = $this->poolManager->getPool();

        $this->poolManager->loadUses();

        $groupLoadedList = $this->getGroupsLoaded($langs);

        if (null === $langs) $langs = [ $this->store->langCurrent ];
        if (null === $groups) $groups = $groupLoadedList;

        $groupIndex = [];
        $groupLoadedIndex = [];

        foreach ( $groupLoadedList as $groupString ) {
            $groupLoadedIndex[ $groupString ] = true;
        }

        $groupList = [];
        foreach ( $groups as $i => $group ) {
            $groupObject = I18nType::group($group);

            $groupString = $groupObject->getValue();

            $groupList[ $i ] = $groupObject;
            $groupIndex[ $groupString ] = true;
        }

        $awordList = [];
        foreach ( $awords as $i => $aword ) {
            if (null === ($awordObject = I18nType::awordOrNull($aword))) {
                $refErrors[ $i ][] = [
                    I18n::E_WRONG_AWORD,
                    'Each of `awords` should be valid aword: [:aword:]',
                    [ 'aword' => $aword ],
                ];

                continue;
            }

            if ($awordObject->isPhrase()) {
                $refErrors[ $i ][] = [
                    I18n::E_WRONG_AWORD,
                    'Each of `awords` should contain valid word, so `aword` have to be string that begins from: `[:prefix:]`',
                    [
                        'prefix' => I18n::AWORD_PREFIX,
                        'aword'  => $aword->getValue(),
                        'i'      => $i,
                    ],
                ];

                continue;
            }

            $awordList[ $i ] = $awordObject;
        }

        $wordList = [];
        foreach ( $awordList as $i => $aword ) {
            $wordObject = $aword->getWord();

            $groupString = $wordObject->getGroup();

            if (! isset($groupLoadedIndex[ $groupString ])) {
                $refErrors[ $i ][] = [
                    I18n::E_FORGOTTEN_GROUP,
                    'The group was not loaded: [:group:]',
                    [ 'group' => $groupString ],
                ];

                continue;
            }

            if (! isset($groupIndex[ $groupString ])) {
                $refErrors[ $i ][] = [
                    I18n::E_EXCLUDED_GROUP,
                    'The group is loaded but excluded: [:group:]',
                    [ 'group' => $groupString ],
                ];

                continue;
            }

            $wordList[ $i ] = $wordObject;
        }

        $langList = [];
        foreach ( $langs as $i => $lang ) {
            $langList[ $i ] = I18nType::lang($lang);
        }

        $poolItemList = $thePool->get(
            $wordList,
            $groupList,
            $langList
        );

        $result = [];

        foreach ( $langList as $langString ) {
            foreach ( $wordList as $i => $word ) {
                $groupString = $word->getGroup();
                $sectionString = $word->getSection();
                $keyString = $word->getKey();

                $index = implode(I18n::INDEX_SEPARATOR, [
                    $langString,
                    $groupString,
                    $sectionString,
                    $keyString,
                ]);

                if (! isset($poolItemList[ $index ])) {
                    $refErrors[ $i ][] = [
                        I18n::E_FORGOTTEN_GROUP,
                        'The word is missing in dictionary: [:index:] / [:langs:]',
                        [
                            'index' => '[ ' . $index . ' ]',
                            'langs' => '[ ' . implode(' ][ ', $langList) . ' ]',
                        ],
                    ];

                    continue;
                }

                $result[ $i ][ $index ] = $poolItemList[ $index ];
            }
        }

        return $result;
    }

    /**
     * @param array<I18nAwordInterface|string>                  $awords
     * @param array<I18nGroupInterface|string>|null             $groups
     * @param array<I18nLangInterface|string>|null              $langs
     * @param array{ 0?: array{ 0: int, 1: string, 2: array } } $refs
     *
     * @return array<int, array<string, I18nPoolItemInterface>>
     */
    public function getOrDefault(array $awords, ?array $groups = null, ?array $langs = null, array $refs = []) : array
    {
        if ([] === $awords) {
            return [];
        }

        $withErrors = array_key_exists(0, $refs);
        if ($withErrors) {
            $refErrors =& $refs[ 0 ];
        }
        $refErrors = [];

        $langDefault = $this->store->langDefault;

        $poolItemList = $this->get($awords, $groups, $langs, $refs);

        if ([] !== $refErrors) {
            $awordsToTryLangDefault = array_intersect_key($awords, $refErrors);
            $groupsToTryLangDefault = $this->getGroupsLoaded($langs);

            $this->poolManager->useGroups($groupsToTryLangDefault, $langDefault);
            $this->poolManager->loadUses();

            $poolItemListLangDefault = $this->get(
                $awordsToTryLangDefault,
                $groupsToTryLangDefault,
                [ $langDefault ]
            );

            foreach ( $poolItemListLangDefault as $i => $poolItems ) {
                foreach ( $poolItems as $index => $poolItem ) {
                    $poolItemList[ $i ][ $index ] = $poolItem;
                }
            }
        }

        return $poolItemList;
    }


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
    ) : array
    {
        $theDebug = Lib::debug();

        $fileLine = $theDebug->file_line();

        $phrasesInterpolated = $this->doPhrasesOrDefault(
            $fileLine,
            $awords,
            $placeholders,
            $groups, $langs
        );

        return $phrasesInterpolated;
    }

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
    ) : array
    {
        $theDebug = Lib::debug();

        $fileLine = $theDebug->file_line();

        $phrasesInterpolated = $this->doPhrases(
            $fileLine,
            $awords, $fallbacks,
            $placeholders,
            $groups, $langs
        );

        return $phrasesInterpolated;
    }


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
    ) : string
    {
        $theDebug = Lib::debug();

        $fileLine = $theDebug->file_line();

        $phrasesInterpolated = $this->doPhrasesOrDefault(
            $fileLine,
            [ $aword ],
            $placeholders,
            $groups, $langs
        );

        [ $phraseInterpolated ] = $phrasesInterpolated;

        return $phraseInterpolated;
    }

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
    ) : ?string
    {
        $theDebug = Lib::debug();

        $fileLine = $theDebug->file_line();

        $fallbacks = $fallback ? [ $fallback[ 0 ] ] : [];

        $phrasesInterpolated = $this->doPhrases(
            $fileLine,
            [ $aword ], $fallbacks,
            $placeholders,
            $groups, $langs
        );

        [ $phraseInterpolated ] = $phrasesInterpolated;

        return $phraseInterpolated;
    }


    /**
     * @param array<I18nAwordInterface|string>      $awords
     * @param array<string, string>[]|null          $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return string[]
     */
    protected function doPhrasesOrDefault(
        array $fileLine,
        array $awords,
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        if (! $awords) {
            return [];
        }

        $placeholders = $placeholders ?? [];

        $theArr = Lib::arr();

        $this->loadUses();

        $awordList = [];
        foreach ( $awords as $i => $aword ) {
            $awordList[ $i ] = I18nType::aword($aword);
        }

        $poolItemLists = $this->getOrDefault($awordList, $groups, $langs, [ &$errorLists ]);

        $phraseList = [];

        if ([] !== $errorLists) {
            foreach ( $errorLists as $i => $errorList ) {
                foreach ( $errorList as [ $errno, $errstr, $errdata ] ) {
                    $errLevel = $this->loggables[ $errno ] ?? 0;

                    $errMessage = [];
                    $errMessage[] = "[ {$fileLine[0]}: {$fileLine[1]} ]";
                    $errMessage[] = $errstr;
                    $errMessage = implode(' ', $errMessage);

                    $errMessage = $this->interpolator->interpolate($errMessage, $errdata);

                    if ($this->logger && $errLevel) {
                        $this->logger->log($errLevel, $errMessage);
                    }

                    if (! isset($poolItemLists[ $i ])) {
                        $phraseList[ $i ] = $awordList[ $i ]->getWordOrPhrase();
                    }
                }
            }
        }

        foreach ( $poolItemLists as $i => $poolItemList ) {
            if ([] !== $poolItemList) {
                /** @var I18nPoolItemInterface $poolItemList */

                $poolItem = reset($poolItemList);

                $phraseList[ $i ] = $poolItem->getPhrase();
            }
        }

        $phrasesInterpolated = [];

        if ([] !== $phraseList) {
            [
                $placeholdersList,
                $placeholdersDict,
            ] = $theArr->kwargs($placeholders);

            foreach ( $phraseList as $i => $phrase ) {
                $phrasePlaceholders = ($placeholdersList[ $i ] ?? []) + $placeholdersDict;

                $phraseInterpolated = $this->interpolator->interpolate($phrase, $phrasePlaceholders);

                $phrasesInterpolated[ $i ] = $phraseInterpolated;
            }
        }

        return $phrasesInterpolated;
    }

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
    protected function doPhrases(
        array $fileLine,
        array $awords, array $fallbacks = [],
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        if (! $awords) {
            return [];
        }

        $placeholders = $placeholders ?? [];

        $theArr = Lib::arr();

        $this->loadUses();

        $poolItemLists = $this->get($awords, $groups, $langs, [ &$errorLists ]);

        $phraseList = [];

        if ([] !== $errorLists) {
            foreach ( $errorLists as $i => $errorList ) {
                foreach ( $errorList as [ $errno, $errstr, $errdata ] ) {
                    $errLevel = $this->loggables[ $errno ] ?? 0;

                    $errMessage = [];
                    $errMessage[] = $errstr;
                    $errMessage = implode(' ', $errMessage);

                    $errMessage = $this->interpolator->interpolate($errMessage, $errdata);

                    if ($this->logger && $errLevel) {
                        $this->logger->log($errLevel, $errMessage);
                    }

                    if (! isset($poolItemLists[ $i ])) {
                        if (! array_key_exists($i, $fallbacks)) {
                            $e = new RuntimeException($errMessage);
                            $e->setFile($fileLine[ 0 ]);
                            $e->setLine($fileLine[ 1 ]);

                            throw $e;
                        }

                        $phraseList[ $i ] = $fallbacks[ $i ];
                    }
                }
            }
        }

        foreach ( $poolItemLists as $i => $poolItemList ) {
            if ([] !== $poolItemList) {
                /** @var I18nPoolItemInterface $poolItemList */

                $poolItem = reset($poolItemList);

                $phraseList[ $i ] = $poolItem->getPhrase();
            }
        }

        $phrasesInterpolated = [];

        if ([] !== $phraseList) {
            [
                $placeholdersList,
                $placeholdersDict,
            ] = $theArr->kwargs($placeholders);

            foreach ( $phraseList as $i => $phrase ) {
                $phrasePlaceholders = ($placeholdersList[ $i ] ?? []) + $placeholdersDict;

                $phraseInterpolated = $this->interpolator->interpolate($phrase, $phrasePlaceholders);

                $phrasesInterpolated[ $i ] = $phraseInterpolated;
            }
        }

        return $phrasesInterpolated;
    }


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
    ) : array
    {
        $theDebug = Lib::debug();

        $fileLine = $theDebug->file_line();

        $choicesInterpolated = $this->doChoicesOrDefault(
            $fileLine,
            $numbers, $awords,
            $placeholders,
            $groups, $langs
        );

        return $choicesInterpolated;
    }

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
    ) : array
    {
        $theDebug = Lib::debug();

        $fileLine = $theDebug->file_line();

        $choicesInterpolated = $this->doChoices(
            $fileLine,
            $numbers, $awords, $fallbacks,
            $placeholders,
            $groups, $langs
        );

        return $choicesInterpolated;
    }

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
    ) : array
    {
        $theDebug = Lib::debug();

        $fileLine = $theDebug->file_line();

        $choicesInterpolated = $this->doChoicesOrDefault(
            $fileLine,
            [ $number ], [ $aword ],
            $placeholders,
            $groups, $langs
        );

        [ $choiceInterpolated ] = $choicesInterpolated;

        return $choiceInterpolated;
    }

    /**
     * @param int|float|string                      $number
     * @param I18nAwordInterface|string             $aword
     * @param array{ 0?: string }                   $fallback
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
    ) : array
    {
        $theDebug = Lib::debug();

        $fileLine = $theDebug->file_line();

        $fallbacks = $fallback ? [ $fallback[ 0 ] ] : [];

        $choicesInterpolated = $this->doChoices(
            $fileLine,
            [ $number ], [ $aword ], $fallbacks,
            $placeholders,
            $groups, $langs
        );

        [ $choiceInterpolated ] = $choicesInterpolated;

        return $choiceInterpolated;
    }

    /**
     * @param array<int|float|string>               $numbers
     * @param array<I18nAwordInterface|string>      $awords
     * @param array<string, string>[]|null          $placeholders
     * @param array<I18nGroupInterface|string>|null $groups
     * @param array<I18nLangInterface|string>|null  $langs
     *
     * @return array{ 0: string, 1: string }[]
     */
    protected function doChoicesOrDefault(
        array $fileLine,
        array $numbers, array $awords,
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        if ([] === $awords) {
            return [];
        }

        $placeholders = $placeholders ?? [];

        $theArr = Lib::arr();
        $theType = Lib::type();

        $this->loadUses();

        $numberList = [];
        foreach ( $numbers as $i => $number ) {
            $numberValid = $theType->numeric($number)->orThrow();

            $numberList[ $i ] = $numberValid;
        }

        $awordList = [];
        foreach ( $awords as $i => $aword ) {
            $awordList[ $i ] = I18nType::aword($aword);
        }

        $poolItemLists = $this->getOrDefault($awordList, $groups, $langs, [ &$errorLists ]);

        $phraseList = [];

        if ([] !== $errorLists) {
            foreach ( $errorLists as $i => $errorList ) {
                foreach ( $errorList as [ $errno, $errstr, $errdata ] ) {
                    $errLevel = $this->loggables[ $errno ] ?? 0;

                    $errMessage = [];
                    $errMessage[] = "[ {$fileLine[0]}: {$fileLine[1]} ]";
                    $errMessage[] = $errstr;
                    $errMessage = implode(' ', $errMessage);

                    $errMessage = $this->interpolator->interpolate($errMessage, $errdata);

                    if ($this->logger && $errLevel) {
                        $this->logger->log($errLevel, $errMessage);
                    }

                    if (! isset($poolItemLists[ $i ])) {
                        $phraseList[ $i ] = $awordList[ $i ]->getWordOrPhrase();
                    }
                }
            }
        }

        foreach ( $poolItemLists as $i => $poolItemList ) {
            if ([] !== $poolItemList) {
                /** @var I18nPoolItemInterface $poolItemList */

                $poolItem = reset($poolItemList);

                $number = $numberList[ $i ];

                $poolItemLang = $poolItem->getLang();
                $poolItemLanguage = $this->getLanguage($poolItemLang);
                $poolItemLanguageChoice = $poolItemLanguage->getChoice();

                $n = $poolItemLanguageChoice->choice($number);

                $phrase = $poolItem->getChoice($n);

                $phraseList[ $i ] = $phrase;
            }
        }

        $choicesInterpolated = [];

        if ([] !== $phraseList) {
            [
                $placeholdersList,
                $placeholdersDict,
            ] = $theArr->kwargs($placeholders);

            foreach ( $phraseList as $i => $phrase ) {
                $number = $numberList[ $i ];

                $phrasePlaceholders = ($placeholdersList[ $i ] ?? []) + $placeholdersDict;

                $phraseInterpolated = $this->interpolator->interpolate($phrase, $phrasePlaceholders);

                $choicesInterpolated[ $i ] = [ $number, $phraseInterpolated ];
            }
        }

        return $choicesInterpolated;
    }

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
    protected function doChoices(
        array $fileLine,
        array $numbers, array $awords, array $fallbacks = [],
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        if ([] === $awords) {
            return [];
        }

        $placeholders = $placeholders ?? [];

        $theArr = Lib::arr();
        $theType = Lib::type();

        $this->loadUses();

        $numberList = [];
        foreach ( $numbers as $i => $number ) {
            $numberValid = $theType->numeric($number)->orThrow();

            $numberList[ $i ] = $numberValid;
        }

        $poolItemLists = $this->get($awords, $groups, $langs, [ &$errorLists ]);

        $phraseList = [];

        if ([] !== $errorLists) {
            foreach ( $errorLists as $i => $errorList ) {
                foreach ( $errorList as [ $errno, $errstr, $errdata ] ) {
                    $errLevel = $this->loggables[ $errno ] ?? 0;

                    $errMessage = [];
                    $errMessage[] = $errstr;
                    $errMessage = implode(' ', $errMessage);

                    $errMessage = $this->interpolator->interpolate($errMessage, $errdata);

                    if ($this->logger && $errLevel) {
                        $this->logger->log($errLevel, $errMessage);
                    }

                    if (! isset($poolItemLists[ $i ])) {
                        if (! array_key_exists($i, $fallbacks)) {
                            $e = new RuntimeException($errMessage);
                            $e->setFile($fileLine[ 0 ]);
                            $e->setLine($fileLine[ 1 ]);

                            throw $e;
                        }

                        $phraseList[ $i ] = $fallbacks[ $i ];
                    }
                }
            }
        }

        foreach ( $poolItemLists as $i => $poolItemList ) {
            if ([] !== $poolItemList) {
                /** @var I18nPoolItemInterface $poolItemList */

                $poolItem = reset($poolItemList);

                $number = $numberList[ $i ];

                $poolItemLang = $poolItem->getLang();
                $poolItemLanguage = $this->getLanguage($poolItemLang);
                $poolItemLanguageChoice = $poolItemLanguage->getChoice();

                $n = $poolItemLanguageChoice->choice($number);

                $phrase = $poolItem->getChoice($n);

                $phraseList[ $i ] = $phrase;
            }
        }

        $choicesInterpolated = [];

        if ([] !== $phraseList) {
            [
                $placeholdersList,
                $placeholdersDict,
            ] = $theArr->kwargs($placeholders);

            foreach ( $phraseList as $i => $phrase ) {
                $number = $numberList[ $i ];

                $phrasePlaceholders = ($placeholdersList[ $i ] ?? []) + $placeholdersDict;

                $phraseInterpolated = $this->interpolator->interpolate($phrase, $phrasePlaceholders);

                $choicesInterpolated[ $i ] = [ $number, $phraseInterpolated ];
            }
        }

        return $choicesInterpolated;
    }
}
