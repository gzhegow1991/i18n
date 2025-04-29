<?php

namespace Gzhegow\I18n\Language;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\Lib\Modules\Php\Result\Result;
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


    /**
     * @return static|bool|null
     */
    public static function from($from, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? static::fromStatic($from, $cur)
            ?? static::fromArray($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromStatic($from, $ctx = null)
    {
        if ($from instanceof static) {
            return Result::ok($ctx, $from);
        }

        return Result::err(
            $ctx,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromArray($from, $ctx = null)
    {
        if (! is_array($from)) {
            return Result::err(
                $ctx,
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

        $langObject = I18nType::lang($lang);

        if (! $theType->string_not_empty($localeString, $locale)) {
            return Result::err(
                $ctx,
                [ 'The `from[locale]` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $theType->string_not_empty($scriptString, $script)) {
            return Result::err(
                $ctx,
                [ 'The `from[script]` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $theType->string_not_empty($titleEnglishString, $titleEnglish)) {
            return Result::err(
                $ctx,
                [ 'The `from[titleEnglish]` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! $theType->string_not_empty($titleNativeString, $titleNative)) {
            return Result::err(
                $ctx,
                [ 'The `from[titleNative]` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $langString = $langObject->getValue();

        $instance = new static();
        $instance->lang = $langString;
        $instance->locale = $localeString;
        $instance->script = $scriptString;
        $instance->titleEnglish = $titleEnglishString;
        $instance->titleNative = $titleNativeString;

        if (null !== $phpLocales) {
            if ((! is_array($phpLocales)) || ([] === $phpLocales)) {
                return Result::err(
                    $ctx,
                    [ 'The `from[phpLocales]` should be non-empty array', $from ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $instance->setPhpLocales($phpLocales);
        }

        if (null !== $choice) {
            if (! is_a($choice, I18nChoiceInterface::class)) {
                return Result::err(
                    $ctx,
                    [ 'The `from[choice]` should be instance of: ' . I18nChoiceInterface::class, $choice ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $instance->setChoice($choice);
        }

        return Result::ok($ctx, $instance);
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
