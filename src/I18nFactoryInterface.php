<?php

namespace Gzhegow\I18n;

use Gzhegow\I18n\Pool\I18nPoolInterface;


interface I18nFactoryInterface
{
    public function newPool() : I18nPoolInterface;
}
