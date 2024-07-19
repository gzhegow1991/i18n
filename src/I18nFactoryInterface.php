<?php

namespace Gzhegow\I18n;

use Gzhegow\I18n\Repo\RepoInterface;
use Gzhegow\I18n\Pool\PoolInterface;
use Gzhegow\I18n\Type\TypeManagerInterface;


interface I18nFactoryInterface
{
    public function newI18n(
        RepoInterface $repo,
        //
        array $config
    ) : I18nInterface;


    public function newTypeManager() : TypeManagerInterface;

    public function newPool() : PoolInterface;
}
