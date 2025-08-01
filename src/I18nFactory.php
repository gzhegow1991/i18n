<?php

namespace Gzhegow\I18n;

use Gzhegow\I18n\Store\I18nStore;


class I18nFactory implements I18nFactoryInterface
{
    public function newStore() : I18nStore
    {
        return new I18nStore();
    }
}
