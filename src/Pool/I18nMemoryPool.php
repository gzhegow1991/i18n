<?php

namespace Gzhegow\I18n\Pool;

use Gzhegow\I18n\I18n;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\I18n\Struct\I18nWordInterface;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Exception\LogicException;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Pool\PoolItem\I18nPoolItemInterface;


class I18nMemoryPool implements I18nPoolInterface
{
    /**
     * @var I18nPoolItemInterface[]
     */
    protected $poolItemList = [];

    /**
     * @var array<string, bool>
     */
    protected $langList = [];
    /**
     * @var array<string, bool>
     */
    protected $groupList = [];


    /**
     * @param (I18nWordInterface|string)[]       $andWordsIn
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return iterable<string, I18nPoolItemInterface|null>
     */
    public function has(
        array $andWordsIn,
        ?array $andGroupsIn = null,
        ?array $andLangsIn = null
    ) : iterable
    {
        if ([] === $andWordsIn) {
            return [];
        }

        $report = [];

        if (null === $andGroupsIn) $andGroupsIn = array_keys($this->groupList);
        if (null === $andLangsIn) $andLangsIn = array_keys($this->langList);

        $groupIndex = [];
        foreach ( $andGroupsIn as $group ) {
            $groupObject = I18nType::group($group);
            $group = $groupObject->getValue();

            $groupIndex[ $group ] = true;
        }

        foreach ( $andLangsIn as $lang ) {
            $langObject = I18nType::lang($lang);
            $lang = $langObject->getValue();

            foreach ( $andWordsIn as $word ) {
                $wordObject = I18nType::word($word);
                $group = $wordObject->getGroup();

                if (! isset($groupIndex[ $group ])) {
                    continue;
                }

                $section = $wordObject->getSection();
                $key = $wordObject->getKey();

                $index = implode(I18n::INDEX_SEPARATOR, [
                    $lang,
                    $group,
                    $section,
                    $key,
                ]);

                $report[ $index ] = $this->poolItemList[ $index ] ?? null;
            }
        }

        return $report;
    }

    /**
     * @param (I18nWordInterface|string)[]       $andWordsIn
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return I18nPoolItemInterface[]
     */
    public function get(
        array $andWordsIn, ?array $andGroupsIn = null, ?array $andLangsIn = null,
        //
        ?int $limit = null, int $offset = 0
    ) : array
    {
        if ([] === $andWordsIn) {
            return [];
        }

        if (null === $andGroupsIn) $andGroupsIn = array_keys($this->groupList);
        if (null === $andLangsIn) $andLangsIn = array_keys($this->langList);

        if ($limit < 1) $limit = null;
        if ($offset < 0) $offset = 0;

        $withLimit = (null !== $limit);

        $groupIndex = [];
        foreach ( $andGroupsIn as $group ) {
            $groupObject = I18nType::group($group);
            $group = $groupObject->getValue();

            $groupIndex[ $group ] = true;
        }

        $poolItemList = [];

        foreach ( $andLangsIn as $lang ) {
            $langObject = I18nType::lang($lang);
            $lang = $langObject->getValue();

            foreach ( $andWordsIn as $word ) {
                $wordObject = I18nType::word($word);

                $group = $wordObject->getGroup();

                if (! isset($groupIndex[ $group ])) {
                    continue;
                }

                if ($offset > 0) {
                    $offset--;

                    continue;
                }

                $section = $wordObject->getSection();
                $key = $wordObject->getKey();

                $index = implode(I18n::INDEX_SEPARATOR, [
                    $lang,
                    $group,
                    $section,
                    $key,
                ]);

                if (isset($this->poolItemList[ $index ])) {
                    $poolItemList[ $index ] = $this->poolItemList[ $index ];

                    if ($withLimit && (--$limit === 0)) {
                        break;
                    }
                }
            }
        }

        return $poolItemList;
    }

    /**
     * @param I18nPoolItemInterface[] $poolItems
     *
     * @return static
     */
    public function set(array $poolItems)
    {
        foreach ( $poolItems as $i => $poolItem ) {
            if (! is_a($poolItem, I18nPoolItemInterface::class)) {
                throw new LogicException(
                    [ 'Each of `words` should be instance of: ' . I18nPoolItemInterface::class, $poolItem, $i ]
                );
            }

            $lang = $poolItem->getLang();
            $word = $poolItem->getWord();

            $group = $word->getGroup();
            $section = $word->getSection();
            $key = $word->getKey();

            $index = implode(I18n::INDEX_SEPARATOR, [
                $lang,
                $group,
                $section,
                $key,
            ]);

            $this->poolItemList[ $index ] = $poolItem;

            $this->langList[ $lang ] = true;
            $this->groupList[ $group ] = true;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function clear()
    {
        $this->poolItemList = [];

        $this->langList = [];
        $this->groupList = [];

        return $this;
    }
}
