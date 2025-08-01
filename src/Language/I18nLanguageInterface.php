<?php

namespace Gzhegow\I18n\Language;

use Gzhegow\I18n\Choice\I18nChoiceInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;


interface I18nLanguageInterface extends
    ToArrayInterface
{
    public function getLang() : string;


    public function getLocale() : string;

    public function getScript() : string;

    public function getTitleEnglish() : string;

    public function getTitleNative() : string;


    /**
     * @return array{
     *     LC_COLLATE?: string|string[],
     *     LC_CTYPE?: string|string[],
     *     LC_MESSAGES?: string|string[],
     *     LC_MONETARY?: string|string[],
     *     LC_NUMERIC?: string|string[],
     *     LC_TIME?: string|string[],
     * }|null
     */
    public function hasPhpLocales() : ?array;

    /**
     * @return array{
     *     LC_COLLATE?: string|string[],
     *     LC_CTYPE?: string|string[],
     *     LC_MESSAGES?: string|string[],
     *     LC_MONETARY?: string|string[],
     *     LC_NUMERIC?: string|string[],
     *     LC_TIME?: string|string[],
     * }
     */
    public function getPhpLocales() : array;

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
    public function setPhpLocales(array $phpLocales);


    public function getChoice() : I18nChoiceInterface;

    /**
     * @return static
     */
    public function setChoice(I18nChoiceInterface $choice);
}
