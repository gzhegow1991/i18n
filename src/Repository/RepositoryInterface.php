<?php

namespace Gzhegow\I18n\Repository;

use Gzhegow\I18n\Struct\WordInterface;
use Gzhegow\I18n\Struct\LangInterface;
use Gzhegow\I18n\Struct\GroupInterface;
use Gzhegow\I18n\Pool\PoolItemInterface;


interface RepositoryInterface
{
    public function isInitialized() : bool;

    public function initialize() : void;


    /**
     * @param (GroupInterface|string)[]|null $andGroupsIn
     * @param (LangInterface|string)[]|null  $andLangsIn
     *
     * @return array{
     *     status: bool,
     *     group: string,
     *     lang: string
     * }[]
     */
    public function hasGroups(
        array $andGroupsIn = null,
        array $andLangsIn = null
    ) : array;

    /**
     * @param (WordInterface|string)[]|null  $andWordsIn
     * @param (GroupInterface|string)[]|null $andGroupsIn
     * @param (LangInterface|string)[]|null  $andLangsIn
     *
     * @return array{
     *     status: bool,
     *     word: string,
     *     group: string,
     *     lang: string
     * }[]
     */
    public function hasWords(
        array $andWordsIn = null,
        array $andGroupsIn = null,
        array $andLangsIn = null
    ) : array;


    /**
     * @param (GroupInterface|string)[]|null $andGroupsIn
     * @param (LangInterface|string)[]|null  $andLangsIn
     *
     * @return iterable<PoolItemInterface[]>
     */
    public function getGroups(
        array $andGroupsIn = null,
        array $andLangsIn = null,
        //
        int $limit = null,
        int $offset = 0
    ) : iterable;

    /**
     * @param (WordInterface|string)[]|null  $andWordsIn
     * @param (GroupInterface|string)[]|null $andGroupsIn
     * @param (LangInterface|string)[]|null  $andLangsIn
     *
     * @return iterable<PoolItemInterface[]>
     */
    public function getWords(
        array $andWordsIn = null,
        array $andGroupsIn = null,
        array $andLangsIn = null,
        //
        int $limit = null,
        int $offset = 0
    ) : iterable;


    /**
     * @param PoolItemInterface[] $poolItems
     *
     * @return iterable<bool[]>
     */
    public function save(array $poolItems) : iterable;

    /**
     * @param PoolItemInterface[] $poolItems
     *
     * @return iterable<bool[]>
     */
    public function delete(array $poolItems) : iterable;
}
