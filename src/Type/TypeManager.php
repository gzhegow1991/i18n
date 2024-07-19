<?php

namespace Gzhegow\I18n\Type;

use Gzhegow\I18n\Struct\Lang;
use Gzhegow\I18n\Struct\Word;
use Gzhegow\I18n\Struct\Group;
use Gzhegow\I18n\Struct\Aword;
use Gzhegow\I18n\Pool\PoolItem;
use Gzhegow\I18n\Struct\Language;
use Gzhegow\I18n\Struct\LangInterface;
use Gzhegow\I18n\Struct\WordInterface;
use Gzhegow\I18n\Struct\GroupInterface;
use Gzhegow\I18n\Struct\AwordInterface;
use Gzhegow\I18n\Pool\PoolItemInterface;
use Gzhegow\I18n\Struct\LanguageInterface;
use Gzhegow\I18n\Repo\File\Struct\FileSource;
use Gzhegow\I18n\Repo\File\Struct\FileSourceInterface;


class TypeManager implements TypeManagerInterface
{
    public function parseLanguage($language) : ?LanguageInterface
    {
        return Language::tryFrom($language);
    }


    public function parsePoolItem($poolItem) : ?PoolItemInterface
    {
        return PoolItem::tryFrom($poolItem);
    }


    public function parseLang($lang) : ?LangInterface
    {
        return Lang::tryFrom($lang);
    }

    public function parseGroup($group) : ?GroupInterface
    {
        return Group::tryFrom($group);
    }


    public function parseAword($aword) : ?AwordInterface
    {
        return Aword::tryFrom($aword);
    }

    public function parseWord($word) : ?WordInterface
    {
        return Word::tryFrom($word);
    }


    public function parseFileSource($fileSource) : ?FileSourceInterface
    {
        return FileSource::tryFrom($fileSource);
    }
}
