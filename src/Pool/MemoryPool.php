<?php

namespace Gzhegow\I18n\Pool;

use Gzhegow\I18n\Type\Type;
use Gzhegow\I18n\Struct\WordInterface;
use Gzhegow\I18n\Struct\LangInterface;
use Gzhegow\I18n\Struct\GroupInterface;
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
     * @param (WordInterface|string)[]       $andWordsIn
     * @param (GroupInterface|string)[]|null $andGroupsIn
     * @param (LangInterface|string)[]|null  $andLangsIn
     *
     * @return iterable<array{
     *      status: bool,
     *      word: WordInterface,
     *      group: GroupInterface,
     *      lang: LangInterface
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
                    $_lang = Type::theLang($lang);
                    $_group = Type::theGroup($group);
                    $_word = Type::theWord($word);

                    $_langString = $_lang->getValue();
                    $_groupString = $_group->getValue();
                    $_wordString = $_word->getValue();

                    $index = $this->index(
                        $_langString,
                        $_groupString,
                        $_wordString
                    );

                    $report[] = [
                        'status' => isset($this->poolItemList[ $index ]),
                        //
                        'word'   => $_wordString,
                        'group'  => $_groupString,
                        'lang'   => $_langString,
                    ];
                }
            }
        }

        return $report;
    }

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

                    $_lang = Type::theLang($lang);
                    $_group = Type::theGroup($group);
                    $_word = Type::theWord($word);

                    $_langString = $_lang->getValue();
                    $_groupString = $_group->getValue();
                    $_wordString = $_word->getValue();

                    $index = $this->index(
                        $_langString,
                        $_groupString,
                        $_wordString
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
    public function set(array $poolItems) // : static
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
    public function clear() // : static
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
