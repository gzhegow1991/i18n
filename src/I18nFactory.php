<?php

namespace Gzhegow\I18n;

use Gzhegow\I18n\Pool\MemoryPool;
use Gzhegow\I18n\Store\I18nStore;
use Gzhegow\I18n\Pool\I18nPoolInterface;


class I18nFactory implements I18nFactoryInterface
{
    public function newStore() : I18nStore
    {
        return new I18nStore();
    }


    public function newPool() : I18nPoolInterface
    {
        return new MemoryPool();
    }
}
