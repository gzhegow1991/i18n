<?php

namespace Gzhegow\I18n\Type;

use Gzhegow\I18n\Struct\I18nLang;
use Gzhegow\I18n\Struct\I18nWord;
use Gzhegow\I18n\Struct\I18nGroup;
use Gzhegow\I18n\Struct\I18nAword;
use Gzhegow\I18n\Pool\I18nPoolItem;
use Gzhegow\I18n\Language\I18nLanguage;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nWordInterface;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Struct\I18nAwordInterface;
use Gzhegow\I18n\Pool\I18nPoolItemInterface;
use Gzhegow\I18n\Language\I18nLanguageInterface;


class I18nTypeManager implements I18nTypeInterface
{
    public function poolItem($poolItem) : I18nPoolItemInterface
    {
        return I18nPoolItem::from($poolItem);
    }

    public function poolItemOrNull($poolItem) : ?I18nPoolItemInterface
    {
        return I18nPoolItem::from($poolItem, Result::parse());
    }


    public function language($language) : I18nLanguageInterface
    {
        return I18nLanguage::from($language);
    }

    public function parseLanguage($language) : ?I18nLanguageInterface
    {
        return I18nLanguage::from($language, Result::parse());
    }


    public function lang($lang) : I18nLangInterface
    {
        return I18nLang::from($lang);
    }

    public function parseLang($lang) : ?I18nLangInterface
    {
        return I18nLang::from($lang, Result::parse());
    }


    public function group($group) : I18nGroupInterface
    {
        return I18nGroup::from($group);
    }

    public function parseGroup($group) : ?I18nGroupInterface
    {
        return I18nGroup::from($group, Result::parse());
    }


    public function aword($aword) : I18nAwordInterface
    {
        return I18nAword::from($aword);
    }

    public function parseAword($aword) : ?I18nAwordInterface
    {
        return I18nAword::from($aword, Result::parse());
    }


    public function word($word) : I18nWordInterface
    {
        return I18nWord::from($word);
    }

    public function parseWord($word) : ?I18nWordInterface
    {
        return I18nWord::from($word, Result::parse());
    }
}
