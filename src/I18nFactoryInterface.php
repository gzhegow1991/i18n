<?php

namespace Gzhegow\I18n;

use Gzhegow\I18n\Store\I18nStore;


interface I18nFactoryInterface
{
    public function newStore() : I18nStore;
}
