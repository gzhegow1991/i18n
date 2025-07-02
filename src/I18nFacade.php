<?php
/**
 * @noinspection PhpUndefinedNamespaceInspection
 * @noinspection PhpUndefinedClassInspection
 */

namespace Gzhegow\I18n;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\I18n\Struct\I18nLang;
use Gzhegow\I18n\Struct\I18nAword;
use Gzhegow\I18n\Struct\I18nGroup;
use Gzhegow\I18n\Pool\I18nPoolInterface;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Exception\LogicException;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Struct\I18nAwordInterface;
use Gzhegow\I18n\Pool\I18nPoolItemInterface;
use Gzhegow\I18n\Exception\RuntimeException;
use Gzhegow\I18n\Language\I18nLanguageInterface;
use Gzhegow\I18n\Repository\I18nRepositoryInterface;


class I18nFacade implements I18nInterface
{
    /**
     * @var I18nFactoryInterface
     */
    protected $factory;
    /**
     * @var I18nRepositoryInterface
     */
    protected $repository;
    /**
     * @var I18nConfig
     */
    protected $config;

    /**
     * @var I18nPoolInterface
     */
    protected $pool;

    /**
     * @var array<string, I18nLanguageInterface>
     */
    protected $languages = [];

    /**
     * @var string
     */
    protected $langCurrent;
    /**
     * @var string
     */
    protected $langDefault;

    /**
     * @var array{0: string[], 1: string}
     */
    protected $loadGroupsQueue = [];
    /**
     * @var array{0: string[], 1: string[], 2: string[]}
     */
    protected $loadWordsQueue = [];
    /**
     * @var array<string, bool>
     */
    protected $loadedGroupsLangs = [];
    /**
     * @var array<string, array<string, bool>>
     */
    protected $loadedGroupLangIndex = [];
    /**
     * @var array<string, array<string, bool>>
     */
    protected $loadedLangGroupIndex = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var array<int, int>
     */
    protected $loggables = [
        I18n::E_FORGOTTEN_GROUP => 0,
        I18n::E_MISSING_WORD    => 0,
        I18n::E_WRONG_AWORD     => 0,
    ];


    public function __construct(
        I18nFactoryInterface $factory,
        //
        I18nRepositoryInterface $repository,
        //
        I18nConfig $config
    )
    {
        $this->factory = $factory;
        $this->repository = $repository;

        $this->config = $config;
        $this->config->validate();

        $this->pool = $this->factory->newPool();

        $this->initializeConfig();
    }


    /**
     * @return static
     */
    protected function initializeConfig()
    {
        $languages = $this->config->languages ?? [];
        $choices = $this->config->choices ?? [];
        $phpLocales = $this->config->phpLocales ?? [];

        $lang = $this->config->lang ?? null;
        $langDefault = $this->config->langDefault ?? null;

        $logger = $this->config->logger ?? null;

        $loggables = [];
        $loggables[ I18n::E_FORGOTTEN_GROUP ] = $this->config->loggables[ I18n::E_FORGOTTEN_GROUP ] ?? null;
        $loggables[ I18n::E_MISSING_WORD ] = $this->config->loggables[ I18n::E_MISSING_WORD ] ?? null;
        $loggables[ I18n::E_WRONG_AWORD ] = $this->config->loggables[ I18n::E_WRONG_AWORD ] ?? null;

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

            $this->languages[ $langString ] = $languageObject;
        }

        if ($lang) {
            $this->setLangCurrent($lang);
        }

        if ($langDefault) {
            $this->setLangDefault($langDefault);
        }

        if ($logger) {
            $this->setLogger($logger);
        }

        if ($loggables) {
            $this->setLoggables($loggables);
        }

        return $this;
    }


    public function getRepository() : I18nRepositoryInterface
    {
        return $this->repository;
    }

    public function getPool() : I18nPoolInterface
    {
        return $this->pool;
    }


    /**
     * @return string[]
     */
    public function getLangs() : array
    {
        return array_keys($this->languages);
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
        foreach ( $this->languages as $lang => $language ) {
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

    /**
     * @return string[]
     */
    public function getLangsHtmlMetaHreflangLines(
        string $stringPrefix = '', string $stringSuffix = '',
        $url = '', $query = null, $fragment = null
    ) : array
    {
        $theUrl = Lib::url();

        $regex = $this->getLangsRegex();

        $link = $theUrl->link($url, $query, $fragment);

        $split = preg_split($regex, $link, 2);
        if (count($split) > 1) {
            $link = implode('{{lang}}', $split);
        }

        $link = ltrim($link, '/');

        $htmlLines = [];

        $langUrlDefault = null;
        foreach ( $this->languages as $lang => $language ) {
            if ($this->langDefault === $lang) {
                $langLink = str_replace('{{lang}}', "{$stringPrefix}{$stringSuffix}", $link);

                $langUrl = $theUrl->url($langLink);
                $langUrlDefault = $langUrl;

            } else {
                $langLink = str_replace('{{lang}}', "{$stringPrefix}{$lang}{$stringSuffix}", $link);

                $langUrl = $theUrl->url("{$langLink}");
            }

            $htmlLines[] = '<link rel="alternate" hreflang="' . $lang . '" href="' . $langUrl . '" />';
        }

        $htmlLines[] = '<link rel="alternate" hreflang="x-default" href="' . $langUrlDefault . '" />';

        return $htmlLines;
    }


    public function hasLang(string $lang) : bool
    {
        return isset($this->languages[ $lang ]);
    }

    public function getLangCurrent() : string
    {
        return $this->langCurrent;
    }

    public function getLangDefault() : string
    {
        return $this->langDefault;
    }

    public function getLangForUrl(?string $lang = null) : ?string
    {
        $lang = $lang ?? $this->langCurrent;

        $result = ($lang === $this->langDefault)
            ? null
            : $lang;

        return $result;
    }


    public function setLangCurrent(string $lang) : I18nInterface
    {
        if ($lang === $this->langCurrent) {
            return $this;
        }

        $language = $this->getLanguage($lang);

        $langString = $language->getLang();

        $this->langCurrent = $langString;

        if ($phpLocales = $language->hasPhpLocales()) {
            foreach ( $phpLocales as $category => $locales ) {
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

        return $this;
    }

    public function setLangDefault(string $lang) : I18nInterface
    {
        $language = $this->getLanguage($lang);

        $langDefaultString = $language->getLang();

        $this->langDefault = $langDefaultString;

        return $this;
    }


    /**
     * @return array<string, I18nLanguageInterface>
     */
    public function getLanguages() : array
    {
        return $this->languages;
    }


    public function hasLanguage(string $lang, ?I18nLanguageInterface &$language = null) : bool
    {
        $language = null;

        if (isset($this->languages[ $lang ])) {
            $language = $this->languages[ $lang ];

            return true;
        }

        return false;
    }

    public function getLanguage(string $lang) : I18nLanguageInterface
    {
        return $this->languages[ $lang ];
    }


    public function getLanguageCurrent() : I18nLanguageInterface
    {
        return $this->getLanguage($this->langCurrent);
    }

    public function getLanguageDefault() : I18nLanguageInterface
    {
        return $this->getLanguage($this->langDefault);
    }


    public function getLocale() : string
    {
        return $this->getLocaleFor($this->langCurrent);
    }

    public function getLocaleDefault() : string
    {
        return $this->getLocaleFor($this->langDefault);
    }

    public function getLocaleFor(string $lang) : ?string
    {
        $locale = null;

        if ($language = $this->getLanguage($lang)) {
            $locale = $language->getLocale();
        }

        return $locale;
    }


    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return \Psr\Log\LoggerInterface|null
     */
    public function setLogger($logger)
    {
        if (null !== $logger) {
            if (! is_a($logger, $class = '\Psr\Log\LoggerInterface')) {
                throw new LogicException(
                    'The `logger` should be instance of: ' . $class
                );
            }
        }

        $loggerCurrent = $this->logger;

        $this->logger = $logger;

        return $loggerCurrent;
    }

    /**
     * @param array<int, int> $loggables
     */
    public function setLoggables(array $loggables) : array
    {
        $loggablesCurrent = $this->loggables;

        $this->loggables = [];
        $this->loggables[ I18n::E_FORGOTTEN_GROUP ] = $loggables[ I18n::E_FORGOTTEN_GROUP ] ?? null;
        $this->loggables[ I18n::E_MISSING_WORD ] = $loggables[ I18n::E_MISSING_WORD ] ?? null;
        $this->loggables[ I18n::E_WRONG_AWORD ] = $loggables[ I18n::E_WRONG_AWORD ] ?? null;

        return $loggablesCurrent;
    }


    public function resetUses() : I18nInterface
    {
        $this->loadGroupsQueue = [];
        $this->loadWordsQueue = [];

        return $this;
    }

    public function useAwords(array $awords, ?array $groups = null, ?array $langs = null) : I18nInterface
    {
        if (! $awords) {
            throw new LogicException(
                [
                    'The `words` should be not empty',
                    $awords,
                ]
            );
        }

        $this->loadWordsQueue[] = [ $awords, $groups, $langs ];

        return $this;
    }

    public function useGroups(array $groups, ?string $lang = null) : I18nInterface
    {
        if (! $groups) {
            throw new LogicException(
                [
                    'The `groups` should be not empty',
                    $groups,
                ]
            );
        }

        $this->loadGroupsQueue[] = [ $groups, $lang ];

        return $this;
    }


    public function clearUsesLoaded() : I18nInterface
    {
        $this->loadGroupsQueue = [];
        $this->loadWordsQueue = [];

        $this->loadedGroupsLangs = [];

        $this->loadedGroupLangIndex = [];
        $this->loadedLangGroupIndex = [];

        $this->pool->clear();

        return $this;
    }

    public function loadUses() : I18nInterface
    {
        $this->loadUsesGroups();
        $this->loadUsesAwords();

        return $this;
    }

    protected function loadUsesGroups() : void
    {
        foreach ( $this->loadGroupsQueue as $i => [ $groups, $lang ] ) {
            $lang = $lang ?? $this->langCurrent;

            $this->getLanguage($lang);

            foreach ( $groups as $groupIdx => $group ) {
                $loadedKey = "{$group}\0{$lang}";

                if (isset($this->loadedGroupsLangs[ $loadedKey ])) {
                    unset($groups[ $groupIdx ]);
                }
            }

            if ($groups) {
                $it = $this->repository->getGroupsIt(
                    $groups,
                    [ $lang ]
                );

                $poolItems = [];
                foreach ( $it as $poolItemsBatch ) {
                    foreach ( $poolItemsBatch as $poolItem ) {
                        $poolItems[] = $poolItem;
                    }
                }

                $this->pool->set($poolItems);

                foreach ( $groups as $group ) {
                    $loadedKey = "{$group}\0{$lang}";

                    $this->loadedGroupsLangs[ $loadedKey ] = true;

                    $this->loadedLangGroupIndex[ $lang ][ $group ] = true;
                    $this->loadedGroupLangIndex[ $group ][ $lang ] = true;
                }
            }

            unset($this->loadGroupsQueue[ $i ]);
        }
    }

    protected function loadUsesAwords() : void
    {
        foreach ( $this->loadWordsQueue as $i => [ $awords, $groups, $langs ] ) {
            $langs = $langs ?? [ $this->langCurrent ];

            foreach ( $langs as $lang ) {
                $this->getLanguage($lang);
            }

            $awordList = [];
            foreach ( $awords as $aword ) {
                $awordList[ $i ] = I18nType::aword($aword);
            }

            $wordList = [];
            foreach ( $awordList as $ii => $aword ) {
                $wordList[ $ii ] = $aword->getWord();
            }

            $it = $this->repository->getWordsIt(
                $wordList,
                $groups,
                $langs
            );

            $poolItems = [];
            foreach ( $it as $poolItemsBatch ) {
                foreach ( $poolItemsBatch as $poolItem ) {
                    $poolItems[] = $poolItem;
                }
            }

            $this->pool->set($poolItems);

            unset($this->loadWordsQueue[ $i ]);
        }
    }


    /**
     * @param (I18nLangInterface|string)[] $langs
     *
     * @return string[]
     */
    public function getGroupsLoaded(?array $langs = null) : array
    {
        if (null === $langs) {
            $groups = $this->loadedGroupLangIndex;

        } else {
            $groups = [];

            foreach ( $langs as $lang ) {
                $langObject = I18nType::lang($lang);

                $langString = $langObject->getValue();

                $groups += $this->loadedLangGroupIndex[ $langString ] ?? [];
            }
        }

        $groups = array_keys($groups);

        return $groups;
    }

    /**
     * @param (I18nGroupInterface|string)[] $groups
     *
     * @return string[]
     */
    public function getLangsLoaded(?array $groups = null) : array
    {
        if (null === $groups) {
            $langs = $this->loadedLangGroupIndex;

        } else {
            $langs = [];

            foreach ( $groups as $group ) {
                $groupObject = I18nType::group($group);

                $groupString = $groupObject->getValue();

                $langs += $this->loadedGroupLangIndex[ $groupString ] ?? [];
            }
        }

        $langs = array_keys($langs);

        return $langs;
    }


    public function interpolate(?string $phrase, ?array $placeholders = null) : ?string
    {
        $placeholders = $placeholders ?? [];

        if (null === $phrase) {
            return null;
        }

        $replacements = [];
        foreach ( $placeholders as $variable => $replacement ) {
            $replacementKey = ''
                . I18n::PLACEHOLDER_BRACES[ 0 ]
                . $variable
                . I18n::PLACEHOLDER_BRACES[ 1 ];

            $replacements[ $replacementKey ] = $replacement;
        }

        $phraseInterpolated = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $phrase
        );

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
    public function phrasesOrDefault(
        array $awords,
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        if (! $awords) {
            return [];
        }

        $result = [];

        $this->loadUses();

        $placeholders = $placeholders ?? [];

        $awordList = [];
        foreach ( $awords as $i => $aword ) {
            $awordList[ $i ] = I18nType::aword($aword);
        }

        [
            $errorList,
            $poolItemList,
        ] = $this->getOrDefault($awordList, $groups, $langs);

        $phraseList = [];

        if ($errorList) {
            $trace = []
                + (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[ 1 ] ?? [])
                + [ 'file' => '[:file:]', 'line' => '[:line:]' ];

            [
                'file' => $file,
                'line' => $line,
            ] = $trace;

            foreach ( $errorList as $i => [ $errno, $errstr, $errdata ] ) {
                $errLevel = $this->loggables[ $errno ] ?? 0;

                $errMessage = [];
                $errMessage[] = '[ ' . "{$file}: {$line}" . ' ]';
                $errMessage[] = $errstr;
                $errMessage = implode(' ', $errMessage);

                $errMessage = $this->interpolate($errMessage, $errdata);

                if ($this->logger && $errLevel) {
                    $this->logger->log($errLevel, $errMessage);
                }

                $phraseList[ $i ] = $awordList[ $i ]->getValue();
            }
        }

        foreach ( $poolItemList as $i => $poolItem ) {
            $phraseList[ $i ] = $poolItem->getPhrase();
        }

        [
            $placeholdersList,
            $placeholdersDict,
        ] = Lib::arr()->kwargs($placeholders);

        foreach ( $phraseList as $i => $phrase ) {
            $phrasePlaceholders = ($placeholdersList[ $i ] ?? []) + $placeholdersDict;

            $phraseInterpolated = $this->interpolate($phrase, $phrasePlaceholders);

            $result[ $i ] = $phraseInterpolated;
        }

        return $result;
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
        if (! $awords) {
            return [];
        }

        $result = [];

        $this->loadUses();

        $placeholders = $placeholders ?? [];

        [
            $errors,
            $poolItems,
        ] = $this->get($awords, $groups, $langs);

        $phrases = [];

        if ($errors) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $traceFile = $trace[ 1 ][ 'file' ] ?? '{file}';
            $traceLine = $trace[ 1 ][ 'line' ] ?? 0;

            foreach ( $errors as $i => [ $errno, $errstr, $errdata ] ) {
                $errLevel = $this->loggables[ $errno ] ?? 0;

                $errMessage = [];
                $errMessage[] = $errstr;
                $errMessage = implode(' ', $errMessage);

                $errMessage = $this->interpolate($errMessage, $errdata);

                if ($this->logger && $errLevel) {
                    $this->logger->log($errLevel, $errMessage);
                }

                if (! array_key_exists($i, $fallbacks)) {
                    $e = new RuntimeException($errMessage);
                    $e->setFile($traceFile);
                    $e->setLine($traceLine);

                    throw $e;
                }

                $phrases[ $i ] = $fallbacks[ $i ];
            }
        }

        foreach ( $poolItems as $i => $poolItem ) {
            $phrases[ $i ] = $poolItem->getPhrase();
        }

        [ $args, $kwargs ] = Lib::arr()->kwargs($placeholders);
        $placeholdersList = $args;
        $placeholdersAll = $kwargs;

        foreach ( $phrases as $i => $phrase ) {
            $phrasePlaceholders = ($placeholdersList[ $i ] ?? []) + $placeholdersAll;

            $phraseInterpolated = $this->interpolate($phrase, $phrasePlaceholders);

            $result[ $i ] = $phraseInterpolated;
        }

        return $result;
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
        [ $phraseInterpolated ] = $this->phrasesOrDefault(
            [ $aword ],
            $placeholders,
            $groups, $langs
        );

        return $phraseInterpolated;
    }

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
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : ?string
    {
        $fallbacks = $fallback ? [ $fallback[ 0 ] ] : [];

        [ $phraseInterpolated ] = $this->phrases(
            [ $aword ], $fallbacks,
            $placeholders,
            $groups, $langs
        );

        return $phraseInterpolated;
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
    public function choicesOrDefault(
        array $numbers, array $awords,
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        if (! $awords) {
            return [];
        }

        $result = [];

        $this->loadUses();

        $placeholders = $placeholders ?? [];

        $numberList = [];
        foreach ( $numbers as $i => $number ) {
            if (null === ($_number = Lib::parse()->numeric($number))) {
                throw new LogicException(
                    [
                        'Each of `numbers` should be valid number or number-string',
                        $number,
                    ]
                );
            }

            $numberList[ $i ] = $_number;
        }

        $awordList = [];
        foreach ( $awords as $i => $aword ) {
            $awordList[ $i ] = I18nType::aword($aword);
        }

        [
            $errorList,
            $poolItems,
        ] = $this->getOrDefault($awordList, $groups, $langs);

        $phraseList = [];

        if ($errorList) {
            $trace = []
                + (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[ 1 ] ?? [])
                + [ 'file' => '[:file:]', 'line' => '[:line:]' ];

            [
                'file' => $file,
                'line' => $line,
            ] = $trace;

            foreach ( $errorList as $i => [ $errno, $errstr, $errdata ] ) {
                $errLevel = $this->loggables[ $errno ] ?? 0;

                $errMessage = [];
                $errMessage[] = '[ ' . "{$file}: {$line}" . ' ]';
                $errMessage[] = $errstr;
                $errMessage = implode(' ', $errMessage);

                $errMessage = $this->interpolate($errMessage, $errdata);

                if ($this->logger && $errLevel) {
                    $this->logger->log($errLevel, $errMessage);
                }

                $phraseList[ $i ] = $awordList[ $i ]->getValue();
            }
        }

        foreach ( $poolItems as $i => $poolItem ) {
            /** @var I18nPoolItemInterface $poolItem */

            $number = $numberList[ $i ];

            $poolItemLang = $poolItem->getLang();
            $poolItemLanguage = $this->getLanguage($poolItemLang);
            $poolItemChoice = $poolItemLanguage->getChoice();

            $n = $poolItemChoice->choice($number);

            $phrase = $poolItem->getChoice($n);

            $phraseList[ $i ] = $phrase;
        }

        [
            $placeholdersList,
            $placeholdersDict,
        ] = Lib::arr()->kwargs($placeholders);

        foreach ( $phraseList as $i => $phrase ) {
            $number = $numberList[ $i ];

            $phrasePlaceholders = ($placeholdersList[ $i ] ?? []) + $placeholdersDict;

            $phraseInterpolated = $this->interpolate($phrase, $phrasePlaceholders);

            $result[ $i ] = [ $number, $phraseInterpolated ];
        }

        return $result;
    }

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
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        if (! $awords) {
            return [];
        }

        $result = [];

        $this->loadUses();

        $placeholders = $placeholders ?? [];

        $_numbers = [];
        foreach ( $numbers as $i => $number ) {
            if (null === ($_number = Lib::parse()->numeric($number))) {
                throw new LogicException(
                    [
                        'Each of `numbers` should be valid number or number-string',
                        $number,
                    ]
                );
            }

            $_numbers[ $i ] = $_number;
        }

        [
            $errors,
            $poolItems,
        ] = $this->get($awords, $groups, $langs);

        $phrases = [];

        if ($errors) {
            $trace = []
                + (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[ 1 ] ?? [])
                + [ 'file' => '[:file:]', 'line' => '[:line:]' ];

            [
                'file' => $file,
                'line' => $line,
            ] = $trace;

            foreach ( $errors as $i => [ $errno, $errstr, $errdata ] ) {
                $errLevel = $this->loggables[ $errno ] ?? 0;

                $errMessage = [];
                $errMessage[] = '[ ' . "{$file}: {$line}" . ' ]';
                $errMessage[] = $errstr;
                $errMessage = implode(' ', $errMessage);

                $errMessage = $this->interpolate($errMessage, $errdata);

                if ($this->logger && $errLevel) {
                    $this->logger->log($errLevel, $errMessage);
                }

                if (! array_key_exists($i, $fallbacks)) {
                    throw new RuntimeException($errMessage);
                }

                $phrases[ $i ] = $fallbacks[ $i ];
            }
        }

        foreach ( $poolItems as $i => $poolItem ) {
            /** @var I18nPoolItemInterface $poolItem */

            $number = $_numbers[ $i ];

            $poolItemLang = $poolItem->getLang();
            $poolItemLanguage = $this->getLanguage($poolItemLang);
            $poolItemChoice = $poolItemLanguage->getChoice();

            $n = $poolItemChoice->choice($number);

            $phrase = $poolItem->getChoice($n);

            $phrases[ $i ] = $phrase;
        }

        [ $args, $kwargs ] = Lib::arr()->kwargs($placeholders);
        $placeholdersList = $args;
        $placeholdersAll = $kwargs;

        foreach ( $phrases as $i => $phrase ) {
            $number = $_numbers[ $i ];

            $phrasePlaceholders = ($placeholdersList[ $i ] ?? []) + $placeholdersAll;

            $phraseInterpolated = $this->interpolate($phrase, $phrasePlaceholders);

            $result[ $i ] = [ $number, $phraseInterpolated ];
        }

        return $result;
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
    public function choiceOrDefault(
        $number, $aword,
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        [ $phraseInterpolated ] = $this->choicesOrDefault(
            [ $number ], [ $aword ],
            $placeholders,
            $groups, $langs
        );

        return $phraseInterpolated;
    }

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
        ?array $placeholders = null,
        ?array $groups = null, ?array $langs = null
    ) : array
    {
        $fallbacks = $fallback ? [ $fallback[ 0 ] ] : [];

        [ $phraseInterpolated ] = $this->choices(
            [ $number ], [ $aword ], $fallbacks,
            $placeholders,
            $groups, $langs
        );

        return $phraseInterpolated;
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
    public function get(array $awords, ?array $groups = null, ?array $langs = null) : array
    {
        if ([] === $awords) {
            return [ [], [] ];
        }

        $this->loadUses();

        $langs = $langs ?? [ $this->langCurrent ];

        $groups = $groups ?? $this->getGroupsLoaded($langs);


        $awordsList = [];
        foreach ( $awords as $i => $aword ) {
            $awordsList[ $i ] = I18nAword::from($aword);
        }

        $groupsList = [];
        foreach ( $groups as $i => $aword ) {
            $groupsList[ $i ] = I18nGroup::from($aword);
        }

        $langsList = [];
        foreach ( $langs as $i => $aword ) {
            $langsList[ $i ] = I18nLang::from($aword);
        }

        $errorList = [];
        $wordsList = [];
        foreach ( $awordsList as $i => $aword ) {
            $awordWord = $aword->hasWord();

            if (null === $awordWord) {
                $errno = I18n::E_WRONG_AWORD;
                $errstr = 'Each `aword` should begin with `aword_prefix` symbol to be translated: [:aword_prefix:] / [:dump:]';
                $errdata = [
                    'aword_prefix' => I18n::AWORD_PREFIX,
                    'dump'         => Lib::debug()->value($aword),
                ];

                $errorList[ $i ] = [ $errno, $errstr, $errdata ];

                continue;
            }

            $awordGroup = $aword->getGroup();

            $found = false;
            foreach ( $groupsList as $group ) {
                $groupString = $group->getValue();

                if ($groupString === $awordGroup) {
                    $found = true;

                    break;
                }
            }
            if (! $found) {
                $errno = I18n::E_FORGOTTEN_GROUP;
                $errstr = 'You forgot to load groups for languages: [:groups:] / [:languages:] / [:dump:]';
                $errdata = [
                    'groups'    => '( ' . implode(', ', $groupsList) . ' )',
                    'languages' => '( ' . implode(', ', $langsList) . ' )',
                    'dump'      => Lib::debug()->value($aword),
                ];

                $errorList[ $i ] = [ $errno, $errstr, $errdata ];

                continue;
            }

            $wordsList[ $i ] = $awordWord;
        }

        $poolItemList = $this->pool->get(
            $wordsList,
            $groupsList,
            $langsList
        );

        foreach ( $wordsList as $i => $word ) {
            if (! isset($poolItemList[ $i ])) {
                $aword = $awordsList[ $i ];

                $errno = I18n::E_MISSING_WORD;
                $errstr = 'This word is missing in the dictionary for languages: [:word:] / [:languages:] / [:dump:]';
                $errdata = [
                    'word'      => $word,
                    'languages' => '( ' . implode(', ', $langsList) . ' )',
                    'dump'      => Lib::debug()->value($aword),
                ];

                $errorList[ $i ] = [ $errno, $errstr, $errdata ];
            }
        }

        return [ $errorList, $poolItemList ];
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
    public function getOrDefault(array $awords, ?array $groups = null, ?array $langs = null) : array
    {
        if (! $awords) {
            return [ [], [] ];
        }

        [ $errors, $items ] = $this->get($awords, $groups, $langs);

        if ($errors) {
            $awordsLangDefault = array_intersect_key($awords, $errors);
            $groupsLangDefault = $this->getGroupsLoaded($langs);

            $this->useGroups($groupsLangDefault, $this->langDefault);
            $this->loadUses();

            [ , $itemsDefault ] = $this->get($awordsLangDefault, $groupsLangDefault, [ $this->langDefault ]);

            foreach ( $itemsDefault as $i => $item ) {
                unset($errors[ $i ]);

                $items[ $i ] = $item;
            }
        }

        return [ $errors, $items ];
    }
}
