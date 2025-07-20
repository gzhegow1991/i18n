<?php

namespace Gzhegow\I18n\Store;

use Gzhegow\I18n\Language\I18nLanguageInterface;


class I18nStore
{
    /**
     * @var bool
     */
    public $isDebug = false;

    /**
     * @var array<string, I18nLanguageInterface>
     */
    public $languages = [];
    /**
     * @var string
     */
    public $langCurrent;
    /**
     * @var string
     */
    public $langDefault;
}
