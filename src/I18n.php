<?php
/**
 * @noinspection PhpUndefinedNamespaceInspection
 * @noinspection PhpUndefinedClassInspection
 */

namespace Gzhegow\I18n;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\I18n\Pool\I18nPoolInterface;
use Gzhegow\I18n\Struct\LangInterface;
use Gzhegow\I18n\Struct\GroupInterface;
use Gzhegow\I18n\Struct\AwordInterface;
use Gzhegow\I18n\Pool\I18nPoolItemInterface;
use Gzhegow\I18n\Exception\LogicException;
use Gzhegow\I18n\Struct\LanguageInterface;
use Gzhegow\I18n\Exception\RuntimeException;
use Gzhegow\I18n\Repository\I18nRepositoryInterface;


class I18n implements I18nInterface
{
    const AWORD_PREFIX       = '@';
    const PLACEHOLDER_BRACES = [ '[:', ':]' ];
    const WORD_SEPARATOR     = '.';


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
     * @var array<string, LanguageInterface>
     */
    protected $languages = [];

    /**
     * @var string
     */
    protected $lang;
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
        I18nInterface::E_FORGOTTEN_GROUP => 0,
        I18nInterface::E_MISSING_WORD    => 0,
        I18nInterface::E_WRONG_AWORD     => 0,
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
    protected function initializeConfig() // : static
    {
        $languages = $this->config->languages ?? [];
        $choices = $this->config->choices ?? [];
        $phpLocales = $this->config->phpLocales ?? [];

        $lang = $this->config->lang ?? null;
        $langDefault = $this->config->langDefault ?? null;

        $logger = $this->config->logger ?? null;

        $loggables = [];
        $loggables[ I18nInterface::E_FORGOTTEN_GROUP ] = $this->config->loggables[ I18nInterface::E_FORGOTTEN_GROUP ] ?? null;
        $loggables[ I18nInterface::E_MISSING_WORD ] = $this->config->loggables[ I18nInterface::E_MISSING_WORD ] ?? null;
        $loggables[ I18nInterface::E_WRONG_AWORD ] = $this->config->loggables[ I18nInterface::E_WRONG_AWORD ] ?? null;

        foreach ( $languages as $languageItem ) {
            $language = I18nType::theLanguage($languageItem);

            $langString = $language->getLang();

            $language->setPhpLocales($phpLocales[ $langString ]);
            $language->setChoice($choices[ $langString ]);

            $this->languages[ $langString ] = $language;
        }

        if ($lang) {
            $this->setLang($lang);
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

    public function getLangsRegexForRoute(string $regexGroupName = null, string $regexBraces = '/', string $regexFlags = '') : string
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
            if ($this->langDefault === $lang) {
                $regex[] = preg_quote('/', '/');
            }

            $regex[] = preg_quote($lang . '/', $regexBraces[ 0 ] ?? '/');
        }

        $regex = implode('|', $regex);

        if ('' !== $regex) {
            if ($withGroupName) {
                $regex = "(?<{$regexGroupName}>" . $regex . ')';

            } elseif ($withBraces || $withFlags) {
                $regex = '(' . $regex . ')';
            }

            if ($withBraces) {
                $braceLeft = $regexBraces[ 0 ] ?? '';
                $braceRight = $regexBraces[ 1 ] ?? $braceLeft;

                $regex = $braceLeft . $regex . $braceRight;
            }

            if ($withFlags) {
                $regex .= $regexFlags;
            }
        }

        return $regex;
    }


    public function getLang() : string
    {
        return $this->lang;
    }

    public function getLangDefault() : string
    {
        return $this->langDefault;
    }

    public function getLangForUrl(string $lang = null) : ?string
    {
        $lang = $lang ?? $this->lang;

        $result = ($lang === $this->langDefault)
            ? null
            : $lang;

        return $result;
    }

    /**
     * @return static
     */
    public function setLang(string $lang) // : static
    {
        $language = $this->getLanguageFor($lang);

        $langString = $language->getLang();

        $this->lang = $langString;

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

    /**
     * @return static
     */
    public function setLangDefault(string $lang) // : static
    {
        $language = $this->getLanguageFor($lang);

        $langDefaultString = $language->getLang();

        $this->langDefault = $langDefaultString;

        return $this;
    }


    public function getLanguages() : array
    {
        return $this->languages;
    }

    public function getLanguage() : LanguageInterface
    {
        return $this->getLanguageFor($this->lang);
    }

    public function getLanguageDefault() : LanguageInterface
    {
        return $this->getLanguageFor($this->langDefault);
    }

    public function getLanguageFor(string $lang) : LanguageInterface
    {
        if ('' === $lang) {
            throw new LogicException('The `lang` should be not empty');
        }

        if (! isset($this->languages[ $lang ])) {
            throw new RuntimeException(
                [
                    'Language not found',
                    $lang,
                ]
            );
        }

        return $this->languages[ $lang ];
    }


    public function getLocale() : string
    {
        return $this->getLocaleFor($this->lang);
    }

    public function getLocaleDefault() : string
    {
        return $this->getLocaleFor($this->langDefault);
    }

    public function getLocaleFor(string $lang) : ?string
    {
        $locale = null;

        if ($language = $this->getLanguageFor($lang)) {
            $locale = $language->getLocale();
        }

        return $locale;
    }


    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return \Psr\Log\LoggerInterface|null
     */
    public function setLogger($logger) // : \Psr\Log\LoggerInterface|null
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
        $this->loggables[ I18nInterface::E_FORGOTTEN_GROUP ] = $loggables[ I18nInterface::E_FORGOTTEN_GROUP ] ?? null;
        $this->loggables[ I18nInterface::E_MISSING_WORD ] = $loggables[ I18nInterface::E_MISSING_WORD ] ?? null;
        $this->loggables[ I18nInterface::E_WRONG_AWORD ] = $loggables[ I18nInterface::E_WRONG_AWORD ] ?? null;

        return $loggablesCurrent;
    }


    /**
     * @return static
     */
    public function resetUses() // : static
    {
        $this->loadGroupsQueue = [];
        $this->loadWordsQueue = [];

        return $this;
    }

    /**
     * @return static
     */
    public function useAwords(array $awords, array $groups = null, array $langs = null) // : static
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

    /**
     * @return static
     */
    public function useGroups(array $groups, string $lang = null) // : static
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


    /**
     * @return static
     */
    public function clearUsesLoaded() // : static
    {
        $this->loadGroupsQueue = [];
        $this->loadWordsQueue = [];

        $this->loadedGroupsLangs = [];

        $this->loadedGroupLangIndex = [];
        $this->loadedLangGroupIndex = [];

        $this->pool->clear();

        return $this;
    }

    /**
     * @return static
     */
    public function loadUses() // : static
    {
        $this->loadUsesGroups();
        $this->loadUsesAwords();

        return $this;
    }

    protected function loadUsesGroups() : void
    {
        foreach ( $this->loadGroupsQueue as $i => [ $groups, $lang ] ) {
            $lang = $lang ?? $this->lang;

            $this->getLanguageFor($lang);

            foreach ( $groups as $groupIdx => $group ) {
                $loadedKey = "{$group}\0{$lang}";

                if (isset($this->loadedGroupsLangs[ $loadedKey ])) {
                    unset($groups[ $groupIdx ]);
                }
            }

            if ($groups) {
                $it = $this->repository->getGroups(
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
            $langs = $langs ?? [ $this->lang ];

            foreach ( $langs as $lang ) {
                $this->getLanguageFor($lang);
            }

            $_awords = I18nType::theAwordList($awords);

            $words = [];
            foreach ( $_awords as $ii => $_aword ) {
                $words[ $ii ] = $_aword->getWord();
            }

            $it = $this->repository->getWords(
                $words,
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
     * @param (LangInterface|string)[] $langs
     *
     * @return string[]
     */
    public function getGroupsLoaded(array $langs = null) : array
    {
        if (null === $langs) {
            $groups = $this->loadedGroupLangIndex;

        } else {
            $groups = [];

            foreach ( $langs as $lang ) {
                $_lang = I18nType::theLang($lang);
                $_langString = $_lang->getValue();

                $groups += $this->loadedLangGroupIndex[ $_langString ] ?? [];
            }
        }

        $groups = array_keys($groups);

        return $groups;
    }

    /**
     * @param (GroupInterface|string)[] $groups
     *
     * @return string[]
     */
    public function getLangsLoaded(array $groups = null) : array
    {
        if (null === $groups) {
            $langs = $this->loadedLangGroupIndex;

        } else {
            $langs = [];

            foreach ( $groups as $group ) {
                $_group = I18nType::theGroup($group);
                $_groupString = $_group->getValue();

                $langs += $this->loadedGroupLangIndex[ $_groupString ] ?? [];
            }
        }

        $langs = array_keys($langs);

        return $langs;
    }


    public function interpolate(?string $phrase, array $placeholders = null) : ?string
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
    ) : array
    {
        if (! $awords) {
            return [];
        }

        $result = [];

        $this->loadUses();

        $placeholders = $placeholders ?? [];

        $_awords = I18nType::theAwordList($awords);

        [
            $errors,
            $poolItems,
        ] = $this->getOrDefault($_awords, $groups, $langs);

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

                $phrases[ $i ] = $_awords[ $i ]->getValue();
            }
        }

        foreach ( $poolItems as $i => $poolItem ) {
            $phrases[ $i ] = $poolItem->getPhrase();
        }

        [ $args, $kwargs ] = Lib::array_kwargs($placeholders);
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
    ) : array
    {
        if (! $awords) {
            return [];
        }

        $result = [];

        $this->loadUses();

        $fallbacks = $fallbacks ?? [];
        $placeholders = $placeholders ?? [];

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
            $phrases[ $i ] = $poolItem->getPhrase();
        }

        [ $args, $kwargs ] = Lib::array_kwargs($placeholders);
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
     * @param AwordInterface|string             $aword
     * @param array<string, string>|null        $placeholders
     * @param array<GroupInterface|string>|null $groups
     * @param array<LangInterface|string>|null  $langs
     */
    public function phraseOrDefault(
        $aword,
        array $placeholders = null,
        array $groups = null, array $langs = null
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
    ) : ?string
    {
        [ $phraseInterpolated ] = $this->phrases(
            [ $aword ], $fallback,
            $placeholders,
            $groups, $langs
        );

        return $phraseInterpolated;
    }


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
            if (null === ($_number = Lib::parse_numeric($number))) {
                throw new LogicException(
                    [
                        'Each of `numbers` should be valid number or number-string',
                        $number,
                    ]
                );
            }

            $_numbers[ $i ] = $_number;
        }

        $_awords = I18nType::theAwordList($awords);

        [
            $errors,
            $poolItems,
        ] = $this->getOrDefault($_awords, $groups, $langs);

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

                $phrases[ $i ] = $_awords[ $i ]->getValue();
            }
        }

        foreach ( $poolItems as $i => $poolItem ) {
            /** @var I18nPoolItemInterface $poolItem */

            $number = $_numbers[ $i ];

            $poolItemLang = $poolItem->getLang();
            $poolItemLanguage = $this->getLanguageFor($poolItemLang);
            $poolItemChoice = $poolItemLanguage->getChoice();

            $n = $poolItemChoice->choice($number);

            $phrase = $poolItem->getChoice($n);

            $phrases[ $i ] = $phrase;
        }

        [ $args, $kwargs ] = Lib::array_kwargs($placeholders);
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
    ) : array
    {
        if (! $awords) {
            return [];
        }

        $result = [];

        $this->loadUses();

        $fallbacks = $fallbacks ?? [];
        $placeholders = $placeholders ?? [];

        $_numbers = [];
        foreach ( $numbers as $i => $number ) {
            if (null === ($_number = Lib::parse_numeric($number))) {
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
            $poolItemLanguage = $this->getLanguageFor($poolItemLang);
            $poolItemChoice = $poolItemLanguage->getChoice();

            $n = $poolItemChoice->choice($number);

            $phrase = $poolItem->getChoice($n);

            $phrases[ $i ] = $phrase;
        }

        [ $args, $kwargs ] = Lib::array_kwargs($placeholders);
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
    ) : array
    {
        [ $phraseInterpolated ] = $this->choices(
            [ $number ], [ $aword ], $fallback,
            $placeholders,
            $groups, $langs
        );

        return $phraseInterpolated;
    }


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
    public function get(array $awords, array $groups = null, array $langs = null) : array
    {
        if (! $awords) {
            return [ [], [] ];
        }

        $this->loadUses();

        $langs = $langs ?? [ $this->lang ];

        $groups = $groups ?? $this->getGroupsLoaded($langs);

        $_awords = I18nType::theAwordList($awords);
        $_groups = I18nType::theGroupList($groups);
        $_langs = I18nType::theLangList($langs);

        $errors = [];

        $_words = [];
        foreach ( $_awords as $i => $aword ) {
            $awordWord = $aword->hasWord();

            if (null === $awordWord) {
                $errno = I18nInterface::E_WRONG_AWORD;
                $errstr = 'Each `aword` should begin with `aword_prefix` symbol to be translated: [:aword_prefix:] / [:dump:]';
                $errdata = [
                    'aword_prefix' => I18n::AWORD_PREFIX,
                    'dump'         => Lib::debug_value($aword),
                ];

                $errors[ $i ] = [ $errno, $errstr, $errdata ];

                continue;
            }

            $awordGroup = $aword->getGroup();

            $found = false;
            foreach ( $_groups as $group ) {
                $groupString = $group->getValue();

                if ($groupString === $awordGroup) {
                    $found = true;

                    break;
                }
            }
            if (! $found) {
                $errno = I18nInterface::E_FORGOTTEN_GROUP;
                $errstr = 'You forgot to load groups for languages: [:groups:] / [:languages:] / [:dump:]';
                $errdata = [
                    'groups'    => '( ' . implode(', ', $_groups) . ' )',
                    'languages' => '( ' . implode(', ', $_langs) . ' )',
                    'dump'      => Lib::debug_value($aword),
                ];

                $errors[ $i ] = [ $errno, $errstr, $errdata ];

                continue;
            }

            $_words[ $i ] = $awordWord;
        }

        $items = $this->pool->get($_words, $_groups, $_langs);

        foreach ( $_words as $i => $word ) {
            if (! isset($items[ $i ])) {
                $aword = $_awords[ $i ];

                $errno = I18nInterface::E_MISSING_WORD;
                $errstr = 'This word is missing in the dictionary for languages: [:word:] / [:languages:] / [:dump:]';
                $errdata = [
                    'word'      => $word,
                    'languages' => '( ' . implode(', ', $_langs) . ' )',
                    'dump'      => Lib::debug_value($aword),
                ];

                $errors[ $i ] = [ $errno, $errstr, $errdata ];
            }
        }

        return [ $errors, $items ];
    }

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
    public function getOrDefault(array $awords, array $groups = null, array $langs = null) : array
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
