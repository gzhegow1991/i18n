<?php

namespace Gzhegow\I18n;

use Gzhegow\I18n\Pool\MemoryPool;
use Gzhegow\I18n\Pool\PoolInterface;
use Gzhegow\I18n\Repository\RepositoryInterface;


class I18nFactory implements I18nFactoryInterface
{
    public function newI18n(
        RepositoryInterface $repository,
        //
        I18nConfig $config
    ) : I18nInterface
    {
        return new I18n(
            $this,
            //
            $repository,
            //
            $config
        );
    }


    public function newPool() : PoolInterface
    {
        return new MemoryPool();
    }
}
