<?php

namespace Gzhegow\I18n\Type;

use Gzhegow\I18n\Struct\I18nKey;
use Gzhegow\I18n\Struct\I18nLang;
use Gzhegow\I18n\Struct\I18nWord;
use Gzhegow\I18n\Struct\I18nGroup;
use Gzhegow\I18n\Struct\I18nAword;
use Gzhegow\I18n\Pool\PoolItem\I18nPoolItem;
use Gzhegow\I18n\Struct\I18nLocale;
use Gzhegow\I18n\Struct\I18nSection;
use Gzhegow\I18n\Language\I18nLanguage;
use Gzhegow\I18n\Struct\I18nKeyInterface;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nWordInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Struct\I18nAwordInterface;
use Gzhegow\I18n\Pool\PoolItem\I18nPoolItemInterface;
use Gzhegow\I18n\Struct\I18nLocaleInterface;
use Gzhegow\I18n\Struct\I18nSectionInterface;
use Gzhegow\I18n\Language\I18nLanguageInterface;


class I18nTypeManager implements I18nTypeInterface
{
    public function poolItem($value) : I18nPoolItemInterface
    {
        return I18nPoolItem::from($value)->orThrow();
    }

    public function poolItemOrNull($value) : ?I18nPoolItemInterface
    {
        return I18nPoolItem::from($value)->orNull();
    }


    public function language($value) : I18nLanguageInterface
    {
        return I18nLanguage::from($value)->orThrow();
    }

    public function languageOrNull($value) : ?I18nLanguageInterface
    {
        return I18nLanguage::from($value)->orNull();
    }


    public function lang($value) : I18nLangInterface
    {
        return I18nLang::from($value)->orThrow();
    }

    public function langOrNull($value) : ?I18nLangInterface
    {
        return I18nLang::from($value)->orNull();
    }


    public function locale($value) : I18nLocaleInterface
    {
        return I18nLocale::from($value)->orThrow();
    }

    public function localeOrNull($value) : ?I18nLocaleInterface
    {
        return I18nLocale::from($value)->orNull();
    }


    public function aword($aword) : I18nAwordInterface
    {
        return I18nAword::from($aword)->orThrow();
    }

    public function awordOrNull($aword) : ?I18nAwordInterface
    {
        return I18nAword::from($aword)->orNull();
    }


    public function word($word) : I18nWordInterface
    {
        return I18nWord::from($word)->orThrow();
    }

    public function wordOrNull($word) : ?I18nWordInterface
    {
        return I18nWord::from($word)->orNull();
    }


    public function group($value) : I18nGroupInterface
    {
        return I18nGroup::from($value)->orThrow();
    }

    public function groupOrNull($value) : ?I18nGroupInterface
    {
        return I18nGroup::from($value)->orNull();
    }


    public function section($value) : I18nSectionInterface
    {
        return I18nSection::from($value)->orThrow();
    }

    public function sectionOrNull($value) : ?I18nSectionInterface
    {
        return I18nSection::from($value)->orNull();
    }


    public function key($value) : I18nKeyInterface
    {
        return I18nKey::from($value)->orThrow();
    }

    public function keyOrNull($value) : ?I18nKeyInterface
    {
        return I18nKey::from($value)->orNull();
    }
}
