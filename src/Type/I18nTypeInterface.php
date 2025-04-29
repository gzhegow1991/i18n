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

    public function parseLanguage($language) : ?I18nLanguageInterface;


    public function lang($lang) : I18nLangInterface;

    public function parseLang($lang) : ?I18nLangInterface;


    public function group($group) : I18nGroupInterface;

    public function parseGroup($group) : ?I18nGroupInterface;


    public function aword($aword) : I18nAwordInterface;

    public function parseAword($aword) : ?I18nAwordInterface;


    public function word($word) : I18nWordInterface;

    public function parseWord($word) : ?I18nWordInterface;
}
