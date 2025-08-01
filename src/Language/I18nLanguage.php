<?php

namespace Gzhegow\I18n\Language;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\I18n\Choice\I18nChoiceInterface;


class I18nLanguage implements I18nLanguageInterface
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


    public function toArray(array $options = []) : array
    {
        return get_object_vars($this);
    }


    /**
     * @return static|Ret<static>
     */
    public static function from($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromArray($from)->orNull($ret);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromStatic($from, ?array $fallback = null)
    {
        if ($from instanceof static) {
            return Ret::ok($fallback, $from);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromArray($from, ?array $fallback = null)
    {
        if (! is_array($from)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $theType = Lib::type();

        $lang = $from[ 'lang' ];

        $locale = $from[ 'locale' ];
        $script = $from[ 'script' ];
        $titleEnglish = $from[ 'titleEnglish' ];
        $titleNative = $from[ 'titleNative' ] ?? null;

        $phpLocales = $from[ 'phpLocales' ] ?? null;
        $choice = $from[ 'choice' ] ?? null;

        $titleNative = $titleNative ?? $titleEnglish;

        if (null === ($langObject = I18nType::langOrNull($lang))) {
            return Ret::throw(
                $fallback,
                [ 'The `from[lang]` should be valid lang', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (null === ($localeObject = I18nType::locale($locale))) {
            return Ret::throw(
                $fallback,
                [ 'The `from[locale]` should be valid locale', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $theType->string_not_empty($script)->isOk([ &$scriptString ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from[script]` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $theType->string_not_empty($titleEnglish)->isOk([ &$titleEnglishString ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from[titleEnglish]` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $theType->string_not_empty($titleNative)->isOk([ &$titleNativeString ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from[titleNative]` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $langString = $langObject->getValue();
        $localeString = $localeObject->getValue();

        $instance = new static();
        $instance->lang = $langString;
        $instance->locale = $localeString;
        $instance->script = $scriptString;
        $instance->titleEnglish = $titleEnglishString;
        $instance->titleNative = $titleNativeString;

        if (null !== $phpLocales) {
            if (! (is_array($phpLocales) && ([] !== $phpLocales))) {
                return Ret::throw(
                    $fallback,
                    [ 'The `from[phpLocales]` should be non-empty array', $from ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $instance->setPhpLocales($phpLocales);
        }

        if (null !== $choice) {
            if (! is_a($choice, I18nChoiceInterface::class)) {
                return Ret::throw(
                    $fallback,
                    [ 'The `from[choice]` should be instance of: ' . I18nChoiceInterface::class, $choice ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $instance->setChoice($choice);
        }

        return Ret::ok($fallback, $instance);
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
     *
     * @return static
     */
    public function setPhpLocales(array $phpLocales)
    {
        $phpLocalesMap = [
            LC_COLLATE  => $phpLocales[ LC_COLLATE ] ?? null,
            LC_CTYPE    => $phpLocales[ LC_CTYPE ] ?? null,
            LC_MONETARY => $phpLocales[ LC_MONETARY ] ?? null,
            LC_NUMERIC  => $phpLocales[ LC_NUMERIC ] ?? null,
            LC_TIME     => $phpLocales[ LC_TIME ] ?? null,
        ];

        if (defined('LC_MESSAGES')) {
            $phpLocalesMap[ LC_MESSAGES ] = $phpLocales[ LC_MESSAGES ] ?? null;
        }

        $phpLocalesMap = array_filter($phpLocalesMap);

        $this->phpLocales = $phpLocalesMap;

        return $this;
    }


    public function getChoice() : I18nChoiceInterface
    {
        return $this->choice;
    }

    /**
     * @return static
     */
    public function setChoice(I18nChoiceInterface $choice)
    {
        $this->choice = $choice;

        return $this;
    }
}
