<?php

namespace Gzhegow\I18n;

use Gzhegow\I18n\Pool\PoolInterface;


interface I18nFactoryInterface
{
    public function newPool() : PoolInterface;
}
