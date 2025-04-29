<?php

namespace Gzhegow\I18n\Pool;

use Gzhegow\I18n\Type\I18nType;
use Gzhegow\I18n\Struct\I18nWordInterface;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Exception\LogicException;


class MemoryPool implements I18nPoolInterface
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
     * @return iterable<array{
     *      status: bool,
     *      word: I18nWordInterface,
     *      group: I18nGroupInterface,
     *      lang: I18nLangInterface
     *  }>
     */
    public function has(
        array $andWordsIn,
        array $andGroupsIn = null,
        array $andLangsIn = null
    ) : iterable
    {
        if (! $andWordsIn) {
            return [];
        }

        $report = [];

        $andGroupsIn = $andGroupsIn ?? array_keys($this->groupList);
        $andLangsIn = $andLangsIn ?? array_keys($this->langList);

        foreach ( $andLangsIn as $lang ) {
            foreach ( $andGroupsIn as $group ) {
                foreach ( $andWordsIn as $word ) {
                    $langObject = I18nType::lang($lang);
                    $groupObject = I18nType::group($group);
                    $wordObject = I18nType::word($word);

                    $langString = $langObject->getValue();
                    $groupString = $groupObject->getValue();
                    $wordString = $wordObject->getValue();

                    $index = $this->index(
                        $langString,
                        $groupString,
                        $wordString
                    );

                    $report[] = [
                        'status' => isset($this->poolItemList[ $index ]),
                        //
                        'word'   => $wordString,
                        'group'  => $groupString,
                        'lang'   => $langString,
                    ];
                }
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
        array $andWordsIn,
        array $andGroupsIn = null,
        array $andLangsIn = null,
        //
        int $limit = null,
        int $offset = 0
    ) : array
    {
        if (! $andWordsIn) {
            return [];
        }

        $poolItems = [];

        $andGroupsIn = $andGroupsIn ?? array_keys($this->groupList);
        $andLangsIn = $andLangsIn ?? array_keys($this->langList);

        if ($limit < 1) $limit = null;
        if ($offset < 0) $offset = 0;

        $withLimit = (null !== $limit);

        foreach ( $andLangsIn as $lang ) {
            foreach ( $andGroupsIn as $group ) {
                foreach ( $andWordsIn as $word ) {
                    if ($offset > 0) {
                        $offset--;

                        continue;
                    }

                    $langObject = I18nType::lang($lang);
                    $groupObject = I18nType::group($group);
                    $wordObject = I18nType::word($word);

                    $langString = $langObject->getValue();
                    $groupString = $groupObject->getValue();
                    $wordString = $wordObject->getValue();

                    $index = $this->index(
                        $langString,
                        $groupString,
                        $wordString
                    );

                    if (isset($this->poolItemList[ $index ])) {
                        $poolItems[] = $this->poolItemList[ $index ];

                        if ($withLimit && (--$limit === 0)) {
                            break;
                        }
                    }
                }
            }
        }

        return $poolItems;
    }


    /**
     * @param I18nPoolItemInterface[] $poolItems
     *
     * @return static
     */
    public function set(array $poolItems)
    {
        foreach ( $poolItems as $poolItem ) {
            if (! is_a($poolItem, I18nPoolItemInterface::class)) {
                throw new LogicException(
                    'Each of `words` should be instance of: ' . I18nPoolItemInterface::class
                );
            }

            $lang = $poolItem->getLang();
            $group = $poolItem->getGroup();
            $word = $poolItem->getWord();

            $index = $this->index(
                $lang,
                $group,
                $word
            );

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


    protected function index(string $lang, string $group, string $word) : string
    {
        return "{$lang}\0{$group}\0{$word}";
    }
}
