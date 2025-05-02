<?php

namespace Gzhegow\I18n\Type;

use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nWordInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Struct\I18nAwordInterface;
use Gzhegow\I18n\Pool\I18nPoolItemInterface;
use Gzhegow\I18n\Language\I18nLanguageInterface;


interface I18nTypeInterface
{
    public function poolItem($poolItem) : I18nPoolItemInterface;

    public function poolItemOrNull($poolItem) : ?I18nPoolItemInterface;


    public function language($language) : I18nLanguageInterface;

    public function languageOrNull($language) : ?I18nLanguageInterface;


    public function lang($lang) : I18nLangInterface;

    public function langOrNull($lang) : ?I18nLangInterface;


    public function group($group) : I18nGroupInterface;

    public function groupOrNull($group) : ?I18nGroupInterface;


    public function aword($aword) : I18nAwordInterface;

    public function awordOrNull($aword) : ?I18nAwordInterface;


    public function word($word) : I18nWordInterface;

    public function wordOrNull($word) : ?I18nWordInterface;
}
