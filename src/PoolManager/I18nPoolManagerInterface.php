<?php

namespace Gzhegow\I18n\PoolManager;

use Gzhegow\I18n\I18nInterface;
use Gzhegow\I18n\Pool\I18nPoolInterface;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Repository\I18nRepositoryInterface;


interface I18nPoolManagerInterface
{
    public function initialize(I18nInterface $i18n) : void;


    public function getPool() : I18nPoolInterface;

    public function getRepository() : I18nRepositoryInterface;


    /**
     * @return static
     */
    public function resetUses();

    /**
     * @return static
     */
    public function resetQueue();

    /**
     * @return static
     */
    public function resetPool();


    /**
     * @return static
     */
    public function useAwords(array $awords, ?array $groups = null, ?array $langs = null);

    /**
     * @return static
     */
    public function useGroups(array $groups, ?string $lang = null);


    /**
     * @return static
     */
    public function loadUses();


    /**
     * @param (I18nLangInterface|string)[] $langs
     *
     * @return string[]
     */
    public function getGroupsLoaded(?array $langs = null) : array;

    /**
     * @param (I18nGroupInterface|string)[] $groups
     *
     * @return string[]
     */
    public function getLangsLoaded(?array $groups = null) : array;
}
