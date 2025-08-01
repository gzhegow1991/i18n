<?php

namespace Gzhegow\I18n\Type;

use Gzhegow\I18n\Struct\I18nKeyInterface;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nWordInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Struct\I18nAwordInterface;
use Gzhegow\I18n\Pool\PoolItem\I18nPoolItemInterface;
use Gzhegow\I18n\Struct\I18nLocaleInterface;
use Gzhegow\I18n\Struct\I18nSectionInterface;
use Gzhegow\I18n\Language\I18nLanguageInterface;


interface I18nTypeInterface
{
    public function poolItem($value) : I18nPoolItemInterface;

    public function poolItemOrNull($value) : ?I18nPoolItemInterface;


    public function language($value) : I18nLanguageInterface;

    public function languageOrNull($value) : ?I18nLanguageInterface;


    public function lang($value) : I18nLangInterface;

    public function langOrNull($value) : ?I18nLangInterface;


    public function locale($value) : I18nLocaleInterface;

    public function localeOrNull($value) : ?I18nLocaleInterface;


    public function aword($aword) : I18nAwordInterface;

    public function awordOrNull($aword) : ?I18nAwordInterface;


    public function word($word) : I18nWordInterface;

    public function wordOrNull($word) : ?I18nWordInterface;


    public function group($value) : I18nGroupInterface;

    public function groupOrNull($value) : ?I18nGroupInterface;


    public function section($value) : I18nSectionInterface;

    public function sectionOrNull($value) : ?I18nSectionInterface;


    public function key($value) : I18nKeyInterface;

    public function keyOrNull($value) : ?I18nKeyInterface;
}
