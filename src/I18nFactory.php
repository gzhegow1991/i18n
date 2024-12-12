<?php

namespace Gzhegow\I18n;

use Gzhegow\I18n\Pool\MemoryPool;
use Gzhegow\I18n\Pool\PoolInterface;


class I18nFactory implements I18nFactoryInterface
{
    public function newPool() : PoolInterface
    {
        return new MemoryPool();
    }
}
