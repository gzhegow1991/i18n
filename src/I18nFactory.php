<?php

namespace Gzhegow\I18n;

use Gzhegow\I18n\Type\Type;
use Gzhegow\I18n\Pool\MemoryPool;
use Gzhegow\I18n\Type\TypeManager;
use Gzhegow\I18n\Repo\RepoInterface;
use Gzhegow\I18n\Pool\PoolInterface;
use Gzhegow\I18n\Type\TypeManagerInterface;


class I18nFactory implements I18nFactoryInterface
{
    public function newI18n(
        RepoInterface $repo,
        //
        array $config
    ) : I18nInterface
    {
        $typeManager = $this->newTypeManager();

        Type::setInstance($typeManager);

        return new I18n(
            $this, $repo,
            //
            $config
        );
    }


    public function newTypeManager() : TypeManagerInterface
    {
        return new TypeManager();
    }

    public function newPool() : PoolInterface
    {
        return new MemoryPool();
    }
}
