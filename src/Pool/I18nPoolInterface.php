<?php

namespace Gzhegow\I18n\Pool;


use Gzhegow\I18n\Struct\WordInterface;
use Gzhegow\I18n\Struct\LangInterface;
use Gzhegow\I18n\Struct\GroupInterface;


interface I18nPoolInterface
{
    /**
     * @param (WordInterface|string)[]       $andWordsIn
     * @param (GroupInterface|string)[]|null $andGroupsIn
     * @param (LangInterface|string)[]|null  $andLangsIn
     *
     * @return iterable<array{
     *     status: bool,
     *     word: WordInterface,
     *     group: GroupInterface,
     *     lang: LangInterface
     * }>
     */
    public function has(
        array $andWordsIn,
        array $andGroupsIn = null,
        array $andLangsIn = null
    ) : iterable;

    /**
     * @param (WordInterface|string)[]       $andWordsIn
     * @param (GroupInterface|string)[]|null $andGroupsIn
     * @param (LangInterface|string)[]|null  $andLangsIn
     *
     * @return I18nPoolItemInterface[]
     */
    public function get(
        array $andWordsIn,
        array $andGroupsIn = null,
        array $andLangsIn = null,
        //
        int $limit = null,
        int $offset = 0
    ) : array;


    /**
     * @param I18nPoolItemInterface[] $poolItems
     *
     * @return static
     */
    public function set(array $poolItems);


    /**
     * @return static
     */
    public function clear();
}
