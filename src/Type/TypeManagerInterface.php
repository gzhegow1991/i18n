<?php

namespace Gzhegow\I18n\Type;

use Gzhegow\I18n\Struct\LangInterface;
use Gzhegow\I18n\Struct\WordInterface;
use Gzhegow\I18n\Struct\GroupInterface;
use Gzhegow\I18n\Struct\AwordInterface;
use Gzhegow\I18n\Pool\PoolItemInterface;
use Gzhegow\I18n\Struct\LanguageInterface;
use Gzhegow\I18n\Repository\File\Struct\FileSourceInterface;


interface TypeManagerInterface
{
    public function parseLanguage($language) : ?LanguageInterface;


    public function parsePoolItem($poolItem) : ?PoolItemInterface;


    public function parseLang($lang) : ?LangInterface;

    public function parseGroup($group) : ?GroupInterface;


    public function parseAword($aword) : ?AwordInterface;

    public function parseWord($word) : ?WordInterface;


    public function parseFileSource($fileSource) : ?FileSourceInterface;
}
