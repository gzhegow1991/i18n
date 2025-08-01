<?php

namespace Gzhegow\I18n\PoolManager;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\I18n;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\I18n\I18nInterface;
use Gzhegow\I18n\Store\I18nStore;
use Gzhegow\I18n\Pool\I18nPoolInterface;
use Gzhegow\I18n\Exception\LogicException;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Repository\I18nRepositoryInterface;


class I18nPoolManager implements I18nPoolManagerInterface
{
    /**
     * @var I18nPoolInterface
     */
    protected $pool;
    /**
     * @var I18nRepositoryInterface
     */
    protected $repository;

    /**
     * @var I18nStore
     */
    protected $store;

    /**
     * @var array{ 0: string[], 1: string }
     */
    protected $loadQueueGroups = [];
    /**
     * @var array{ 0: string[], 1: string[], 2: string[] }
     */
    protected $loadQueueWords = [];

    /**
     * @var array<string, string>
     */
    protected $loadedLangGroupList = [];


    public function __construct(
        I18nPoolInterface $pool,
        I18nRepositoryInterface $repository
    )
    {
        $this->pool = $pool;
        $this->repository = $repository;
    }

    public function initialize(I18nInterface $i18n) : void
    {
        $this->store = $i18n->getStore();
    }


    public function getPool() : I18nPoolInterface
    {
        return $this->pool;
    }

    public function getRepository() : I18nRepositoryInterface
    {
        return $this->repository;
    }


    /**
     * @return static
     */
    public function resetUses()
    {
        $this->resetQueue();
        $this->resetPool();

        return $this;
    }

    /**
     * @return static
     */
    public function resetQueue()
    {
        $this->loadQueueGroups = [];
        $this->loadQueueWords = [];

        return $this;
    }

    /**
     * @return static
     */
    public function resetPool()
    {
        $this->loadedLangGroupList = [];

        $this->pool->clear();

        return $this;
    }


    /**
     * @return static
     */
    public function useAwords(array $awords, ?array $groups = null, ?array $langs = null)
    {
        if ([] === $awords) {
            throw new LogicException(
                [ 'The `words` should be array not empty', $awords ]
            );
        }

        $this->loadQueueWords[] = [ $awords, $groups, $langs ];

        return $this;
    }

    /**
     * @return static
     */
    public function useGroups(array $groups, ?string $lang = null)
    {
        if ([] === $groups) {
            throw new LogicException(
                [ 'The `groups` should be array not empty', $groups ]
            );
        }

        $this->loadQueueGroups[] = [ $groups, $lang ];

        return $this;
    }


    /**
     * @return static
     */
    public function loadUses()
    {
        $this->loadUsesGroups();
        $this->loadUsesAwords();

        return $this;
    }

    protected function loadUsesGroups() : void
    {
        $theType = Lib::type();

        foreach ( $this->loadQueueGroups as $i => [ $groups, $lang ] ) {
            $lang = $lang ?? $this->store->langCurrent;

            $theType->key_exists($lang, $this->store->languages)->orThrow();

            foreach ( $groups as $ii => $group ) {
                $index = implode(I18n::INDEX_SEPARATOR, [
                    $lang,
                    $group,
                ]);

                if (isset($this->loadedLangGroupList[ $index ])) {
                    unset($groups[ $ii ]);
                }
            }

            if ([] === $groups) {
                continue;
            }

            $it = $this->repository->getGroupsIt(
                $groups,
                [ $lang ]
            );

            $poolItems = [];
            foreach ( $it as $poolItemsBatch ) {
                foreach ( $poolItemsBatch as $poolItem ) {
                    $poolItems[] = $poolItem;
                }
            }

            $this->pool->set($poolItems);

            foreach ( $groups as $group ) {
                $index = implode(I18n::INDEX_SEPARATOR, [
                    $lang,
                    $group,
                ]);

                $this->loadedLangGroupList[ $index ] = [ $lang, $group ];
            }

            unset($this->loadQueueGroups[ $i ]);
        }
    }

    protected function loadUsesAwords() : void
    {
        $theType = Lib::type();

        foreach ( $this->loadQueueWords as $i => [ $awords, $groups, $langs ] ) {
            $langs = $langs ?? [ $this->store->langCurrent ];

            foreach ( $langs as $lang ) {
                $theType->key_exists($lang, $this->store->languages)->orThrow();
            }

            $awordList = [];
            foreach ( $awords as $ii => $aword ) {
                $awordList[ $ii ] = I18nType::aword($aword);
            }

            $wordList = [];
            foreach ( $awordList as $ii => $aword ) {
                $wordList[ $ii ] = $aword->getWord();
            }

            $it = $this->repository->getWordsIt(
                $wordList,
                $groups,
                $langs
            );

            $poolItems = [];
            foreach ( $it as $poolItemsBatch ) {
                foreach ( $poolItemsBatch as $poolItem ) {
                    $poolItems[] = $poolItem;
                }
            }

            $this->pool->set($poolItems);

            unset($this->loadQueueWords[ $i ]);
        }
    }


    /**
     * @param (I18nLangInterface|string)[] $langs
     *
     * @return string[]
     */
    public function getGroupsLoaded(?array $langs = null) : array
    {
        $groupsLoaded = [];

        if (null === $langs) {
            foreach ( $this->loadedLangGroupList as [ $lang, $group ] ) {
                $groupsLoaded[] = $group;
            }

        } else {
            $theType = Lib::type();

            $langIndex = [];
            foreach ( $langs as $lang ) {
                $langString = $theType->string_not_empty($lang)->orThrow();

                $langIndex[ $langString ] = true;
            }

            foreach ( $this->loadedLangGroupList as [ $lang, $group ] ) {
                if (! isset($langIndex[ $lang ])) {
                    continue;
                }

                $groupsLoaded[] = $group;
            }
        }

        return $groupsLoaded;
    }

    /**
     * @param (I18nGroupInterface|string)[] $groups
     *
     * @return string[]
     */
    public function getLangsLoaded(?array $groups = null) : array
    {
        $langsLoaded = [];

        if (null === $groups) {
            foreach ( $this->loadedLangGroupList as [ $lang, $group ] ) {
                $langsLoaded[] = $lang;
            }

        } else {
            $theType = Lib::type();

            $groupIndex = [];
            foreach ( $groups as $group ) {
                $groupString = $theType->string_not_empty($group)->orThrow();

                $groupIndex[ $groupString ] = true;
            }

            foreach ( $this->loadedLangGroupList as [ $lang, $group ] ) {
                if (! isset($groupIndex[ $group ])) {
                    continue;
                }

                $langsLoaded[] = $lang;
            }
        }

        return $langsLoaded;
    }
}
