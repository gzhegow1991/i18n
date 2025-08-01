<?php

namespace Gzhegow\I18n\Repository;

use Gzhegow\I18n\Struct\I18nWordInterface;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Pool\PoolItem\I18nPoolItemInterface;


interface I18nRepositoryInterface
{
    public function isInitialized() : bool;

    public function initialize() : void;


    /**
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return array{
     *     status: bool,
     *     group: string,
     *     lang: string
     * }[]
     */
    public function hasGroups(
        ?array $andGroupsIn = null,
        ?array $andLangsIn = null
    ) : array;

    /**
     * @param (I18nWordInterface|string)[]|null  $andWordsIn
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return array{
     *     status: bool,
     *     word: string,
     *     group: string,
     *     lang: string
     * }[]
     */
    public function hasWords(
        ?array $andWordsIn = null,
        ?array $andGroupsIn = null,
        ?array $andLangsIn = null
    ) : array;


    /**
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return I18nPoolItemInterface[]
     */
    public function getGroups(
        ?array $andGroupsIn = null,
        ?array $andLangsIn = null,
        //
        ?int $limit = null,
        int $offset = 0
    ) : array;

    /**
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return iterable<I18nPoolItemInterface[]>
     */
    public function getGroupsIt(
        ?array $andGroupsIn = null,
        ?array $andLangsIn = null,
        //
        ?int $limit = null,
        int $offset = 0
    ) : iterable;


    /**
     * @param (I18nWordInterface|string)[]|null  $andWordsIn
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return I18nPoolItemInterface[]
     */
    public function getWords(
        ?array $andWordsIn = null,
        ?array $andGroupsIn = null,
        ?array $andLangsIn = null,
        //
        ?int $limit = null,
        int $offset = 0
    ) : array;

    /**
     * @param (I18nWordInterface|string)[]|null  $andWordsIn
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return iterable<I18nPoolItemInterface[]>
     */
    public function getWordsIt(
        ?array $andWordsIn = null,
        ?array $andGroupsIn = null,
        ?array $andLangsIn = null,
        //
        ?int $limit = null,
        int $offset = 0
    ) : iterable;


    /**
     * @param I18nPoolItemInterface[] $poolItems
     *
     * @return bool[]
     */
    public function save(array $poolItems) : array;

    /**
     * @param I18nPoolItemInterface[] $poolItems
     *
     * @return iterable<bool[]>
     */
    public function saveIt(array $poolItems) : iterable;


    /**
     * @param I18nPoolItemInterface[] $poolItems
     *
     * @return bool[]
     */
    public function delete(array $poolItems) : array;

    /**
     * @param I18nPoolItemInterface[] $poolItems
     *
     * @return iterable<bool[]>
     */
    public function deleteIt(array $poolItems) : iterable;
}
