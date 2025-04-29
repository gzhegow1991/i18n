<?php

namespace Gzhegow\I18n\Pool;


use Gzhegow\I18n\Struct\I18nWordInterface;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;


interface I18nPoolInterface
{
    /**
     * @param (I18nWordInterface|string)[]       $andWordsIn
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return iterable<array{
     *     status: bool,
     *     word: I18nWordInterface,
     *     group: I18nGroupInterface,
     *     lang: I18nLangInterface
     * }>
     */
    public function has(
        array $andWordsIn,
        array $andGroupsIn = null,
        array $andLangsIn = null
    ) : iterable;

    /**
     * @param (I18nWordInterface|string)[]       $andWordsIn
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
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
