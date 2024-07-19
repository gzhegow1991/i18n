<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\I18n\Lib;
use Gzhegow\I18n\Type\Type;
use Gzhegow\I18n\Choice\ChoiceInterface;


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
     * @var ChoiceInterface
     */
    protected $choice;


    private function __construct()
    {
    }

    public static function from($from) : self
    {
        return static::tryFrom($from);
    }

    public static function tryFrom($from) : ?self
    {
        $instance = null
            ?? static::tryFromInstance($from)
            ?? static::tryFromArray($from);

        return $instance;
    }

    public static function tryFromInstance($from) : ?self
    {
        if (! is_a($from, static::class)) return null;

        return $from;
    }

    public static function tryFromArray($from) : ?self
    {
        if (! is_array($from)) {
            return null;
        }

        $lang = $from[ 'lang' ];

        $locale = $from[ 'locale' ];
        $script = $from[ 'script' ];
        $titleEnglish = $from[ 'titleEnglish' ];
        $titleNative = $from[ 'titleNative' ] ?? null;

        $phpLocales = $from[ 'phpLocales' ] ?? null;
        $choice = $from[ 'choice' ] ?? null;

        $titleNative = $titleNative ?? $titleEnglish;

        if (null === ($_lang = Type::parseLang($lang))) {
            return null;
        }

        if (null === ($_locale = Lib::parse_string($locale))) {
            return null;
        }

        if (null === ($_script = Lib::parse_string($script))) {
            return null;
        }

        if (null === ($_titleEnglish = Lib::parse_string($titleEnglish))) {
            return null;
        }

        if (null === ($_titleNative = Lib::parse_string($titleNative))) {
            return null;
        }

        $_langString = $_lang->getValue();

        $instance = new static();

        $instance->lang = $_langString;

        $instance->locale = $_locale;
        $instance->script = $_script;
        $instance->titleEnglish = $_titleEnglish;
        $instance->titleNative = $_titleNative;

        if (null !== $phpLocales) {
            if (! is_array($phpLocales)) {
                return null;
            }

            $instance->setPhpLocales($phpLocales);
        }

        if (null !== $choice) {
            if (! is_a($choice, ChoiceInterface::class)) {
                return null;
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


    public function getChoice() : ChoiceInterface
    {
        return $this->choice;
    }

    public function setChoice(ChoiceInterface $choice) : void
    {
        $this->choice = $choice;
    }


    public function __debugInfo()
    {
        return get_object_vars($this);
    }
}
