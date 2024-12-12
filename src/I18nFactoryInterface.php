<?php

namespace Gzhegow\I18n;

use Gzhegow\I18n\Pool\PoolInterface;
use Gzhegow\I18n\Repository\RepositoryInterface;


interface I18nFactoryInterface
{
    public function newI18n(
        RepositoryInterface $repository,
        //
        I18nConfig $config
    ) : I18nInterface;


    public function newPool() : PoolInterface;
}
