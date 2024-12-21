<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\I18n\Choice\I18nChoiceInterface;
use Gzhegow\I18n\Exception\LogicException;


class Language implements LanguageInterface
{
    /**
     * @var string
     */
    protected $lang;

    /**
     * @var string
     */
    protected $locale;
    /**
     * @var string
     */
    protected $script;
    /**
     * @var string
     */
    protected $titleEnglish;
    /**
     * @var string
     */
    protected $titleNative;

    /**
     * @var array
     */
    protected $phpLocales;

    /**
     * @var I18nChoiceInterface
     */
    protected $choice;


    private function __construct()
    {
    }


    public static function from($from) : self
    {
        $instance = static::tryFrom($from, $error);

        if (null === $instance) {
            throw $error;
        }

        return $instance;
    }

    public static function tryFrom($from, \Throwable &$last = null) : ?self
    {
        $last = null;

        Lib::php_errors_start($b);

        $instance = null
            ?? static::tryFromInstance($from)
            ?? static::tryFromArray($from);

        $errors = Lib::php_errors_end($b);

        if (null === $instance) {
            foreach ( $errors as $error ) {
                $last = new LogicException($error, null, $last);
            }
        }

        return $instance;
    }


    public static function tryFromInstance($from) : ?self
    {
        if (! is_a($from, static::class)) {
            return Lib::php_error(
                [
                    'The `from` should be instance of: ' . static::class,
                    $from,
                ]
            );
        }

        return $from;
    }

    public static function tryFromArray($from) : ?self
    {
        if (! is_array($from)) {
            return Lib::php_error(
                [
                    'The `from` should be non-empty string',
                    $from,
                ]
            );
        }

        $lang = $from[ 'lang' ];

        $locale = $from[ 'locale' ];
        $script = $from[ 'script' ];
        $titleEnglish = $from[ 'titleEnglish' ];
        $titleNative = $from[ 'titleNative' ] ?? null;

        $phpLocales = $from[ 'phpLocales' ] ?? null;
        $choice = $from[ 'choice' ] ?? null;

        $titleNative = $titleNative ?? $titleEnglish;

        if (null === ($_lang = I18nType::parseLang($lang))) {
            return Lib::php_error(
                [
                    'The `from[lang]` should be valid `lang`',
                    $from,
                ]
            );
        }

        if (null === ($_locale = Lib::parse_string_not_empty($locale))) {
            return Lib::php_error(
                [
                    'The `from[locale]` should be non-empty string',
                    $from,
                ]
            );
        }

        if (null === ($_script = Lib::parse_string_not_empty($script))) {
            return Lib::php_error(
                [
                    'The `from[script]` should be non-empty string',
                    $from,
                ]
            );
        }

        if (null === ($_titleEnglish = Lib::parse_string_not_empty($titleEnglish))) {
            return Lib::php_error(
                [
                    'The `from[titleEnglish]` should be non-empty string',
                    $from,
                ]
            );
        }

        if (null === ($_titleNative = Lib::parse_string_not_empty($titleNative))) {
            return Lib::php_error(
                [
                    'The `from[titleNative]` should be non-empty string',
                    $from,
                ]
            );
        }

        $_langString = $_lang->getValue();

        $instance = new static();

        $instance->lang = $_langString;

        $instance->locale = $_locale;
        $instance->script = $_script;
        $instance->titleEnglish = $_titleEnglish;
        $instance->titleNative = $_titleNative;

        if (null !== $phpLocales) {
            if (! is_array($phpLocales) || ! $phpLocales) {
                return Lib::php_error(
                    [
                        'The `from[phpLocales]` should be non-empty array',
                        $from,
                    ]
                );
            }

            $instance->setPhpLocales($phpLocales);
        }

        if (null !== $choice) {
            if (! is_a($choice, I18nChoiceInterface::class)) {
                return Lib::php_error(
                    [
                        'The `from[choice]` should be instance of: ' . I18nChoiceInterface::class,
                        $choice,
                    ]
                );
            }

            $instance->setChoice($choice);
        }

        return $instance;
    }


    public function getLang() : string
    {
        return $this->lang;
    }


    public function getLocale() : string
    {
        return $this->locale;
    }

    public function getScript() : string
    {
        return $this->script;
    }

    public function getTitleEnglish() : string
    {
        return $this->titleEnglish;
    }

    public function getTitleNative() : string
    {
        return $this->titleNative;
    }


    /**
     * @return array{
     *     LC_COLLATE?: string|string[],
     *     LC_CTYPE?: string|string[],
     *     LC_NUMERIC?: string|string[],
     *     LC_TIME?: string|string[],
     *     LC_MONETARY?: string|string[],
     *     LC_MESSAGES?: string|string[],
     * }|null
     */
    public function hasPhpLocales() : ?array
    {
        return $this->phpLocales;
    }

    /**
     * @return array{
     *     LC_COLLATE?: string|string[],
     *     LC_CTYPE?: string|string[],
     *     LC_NUMERIC?: string|string[],
     *     LC_TIME?: string|string[],
     *     LC_MONETARY?: string|string[],
     *     LC_MESSAGES?: string|string[],
     * }
     */
    public function getPhpLocales() : array
    {
        return $this->phpLocales;
    }

    /**
     * @param array{
     *     LC_COLLATE?: string|string[],
     *     LC_CTYPE?: string|string[],
     *     LC_NUMERIC?: string|string[],
     *     LC_TIME?: string|string[],
     *     LC_MONETARY?: string|string[],
     *     LC_MESSAGES?: string|string[],
     * } $phpLocales
     */
    public function setPhpLocales(array $phpLocales) : void
    {
        $_phpLocales = [
            LC_COLLATE  => $phpLocales[ LC_COLLATE ] ?? null,
            LC_CTYPE    => $phpLocales[ LC_CTYPE ] ?? null,
            LC_MONETARY => $phpLocales[ LC_MONETARY ] ?? null,
            LC_NUMERIC  => $phpLocales[ LC_NUMERIC ] ?? null,
            LC_TIME     => $phpLocales[ LC_TIME ] ?? null,
        ];

        if (defined('LC_MESSAGES')) {
            $_phpLocales[ LC_MESSAGES ] = $phpLocales[ LC_MESSAGES ] ?? null;
        }

        $_phpLocales = array_filter($_phpLocales);

        $this->phpLocales = $_phpLocales;
    }


    public function getChoice() : I18nChoiceInterface
    {
        return $this->choice;
    }

    public function setChoice(I18nChoiceInterface $choice) : void
    {
        $this->choice = $choice;
    }
}
