<?php

namespace Gzhegow\I18n\Repository\File;

use Gzhegow\I18n\I18n;
use Gzhegow\I18n\Pool\I18nPoolItem;
use Gzhegow\I18n\Struct\I18nWordInterface;
use Gzhegow\I18n\Struct\I18nLangInterface;
use Gzhegow\I18n\Struct\I18nGroupInterface;
use Gzhegow\I18n\Exception\LogicException;
use Gzhegow\I18n\Pool\I18nPoolItemInterface;
use Gzhegow\I18n\Exception\RuntimeException;
use Gzhegow\I18n\Repository\I18nRepositoryInterface;
use Gzhegow\I18n\Repository\File\FileSource\I18nFileSourceInterface;


abstract class AbstractI18nFileRepository implements I18nRepositoryInterface
{
    /**
     * @var string
     */
    protected $langDir;


    public function __construct(string $langDir)
    {
        if (! (is_dir($langDir))) {
            throw new LogicException(
                'The `langDir` should be existing directory: ' . $langDir
            );
        }

        $this->langDir = realpath($langDir);
    }


    public function isInitialized() : bool
    {
        return true;
    }

    public function initialize() : void
    {
        //
    }


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
        array $andGroupsIn = null,
        array $andLangsIn = null
    ) : array
    {
        if (! ($andLangsIn || $andGroupsIn)) {
            // > @gzhegow, file repository cannot select by keys, so the languages and groups is required

            return [];
        }

        $report = [];

        $andGroupsIn = $andGroupsIn ?? [];
        $andLangsIn = $andLangsIn ?? [];

        $fileSources = $this->buildFileSources($andLangsIn, $andGroupsIn);

        $i = 0;
        foreach ( $fileSources as $fileSource ) {
            $report[ $i++ ] = [
                'status' => (bool) $fileSource->hasRealpath(),
                'group'  => $fileSource->getGroup(),
                'lang'   => $fileSource->getLang(),
            ];
        }

        return $report;
    }

    /**
     * @param (I18nWordInterface|string)[]|null  $andWordsIn
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return array{
     *     status: bool,
     *     word: I18nWordInterface,
     *     group: I18nGroupInterface,
     *     lang: I18nLangInterface
     * }[]
     */
    public function hasWords(
        array $andWordsIn = null,
        array $andGroupsIn = null,
        array $andLangsIn = null
    ) : array
    {
        if (! ($andWordsIn || $andGroupsIn || $andLangsIn)) {
            // > @gzhegow, file repository cannot select by keys, so the languages and groups is required

            return [];
        }

        $report = [];

        $andWordsIn = $andWordsIn ?? [];
        $andGroupsIn = $andGroupsIn ?? [];
        $andLangsIn = $andLangsIn ?? [];

        $wordsToFetch = $this->buildWords($andWordsIn, $andGroupsIn);

        $fileSources = $this->buildFileSources($andLangsIn, $andGroupsIn);

        $generator = $this->loadItemsFromFilesIt($fileSources);

        $i = 0;
        foreach ( $generator as $data ) {
            $fileSource = $data[ 'fileSource' ];
            $items = $data[ 'items' ];

            foreach ( $wordsToFetch as $word => $bool ) {
                if (array_key_exists($word, $items)) {
                    $report[ $i++ ] = [
                        'status' => true,
                        'word'   => $word,
                        'group'  => $fileSource->getGroup(),
                        'lang'   => $fileSource->getLang(),
                    ];

                } else {
                    $report[ $i++ ] = [
                        'status' => false,
                        'word'   => $word,
                        'group'  => $fileSource->getGroup(),
                        'lang'   => $fileSource->getLang(),
                    ];
                }
            }
        }

        return $report;
    }


    /**
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return I18nPoolItemInterface[]
     */
    public function getGroups(
        array $andGroupsIn = null,
        array $andLangsIn = null,
        //
        int $limit = null,
        int $offset = 0
    ) : array
    {
        $gen = $this->getGroupsIt(
            $andGroupsIn, $andLangsIn,
            $limit, $offset
        );

        $poolItemsList = [];

        foreach ( $gen as $poolItemsChunk ) {
            $poolItemsList = array_merge(
                $poolItemsList,
                $poolItemsChunk
            );
        }

        return $poolItemsList;
    }

    /**
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return iterable<I18nPoolItemInterface[]>
     */
    public function getGroupsIt(
        array $andGroupsIn = null,
        array $andLangsIn = null,
        //
        int $limit = null,
        int $offset = 0
    ) : iterable
    {
        if (! ($andLangsIn && $andGroupsIn)) {
            // > @gzhegow, file repository cannot select by keys, so the languages and groups is required

            return;
        }

        $andGroupsIn = $andGroupsIn ?? [];
        $andLangsIn = $andLangsIn ?? [];

        if ($limit < 1) $limit = null;
        if ($offset < 0) $offset = 0;

        $withLimit = (null !== $limit);

        $fileSources = $this->buildFileSources($andLangsIn, $andGroupsIn);

        $generator = $this->loadItemsFromFilesIt($fileSources);

        $poolItems = [];
        foreach ( $generator as $data ) {
            $items = $data[ 'items' ];

            foreach ( $items as $poolItem ) {
                if ($offset > 0) {
                    $offset--;

                    continue;
                }

                $poolItems[] = $poolItem;

                if ($withLimit && (--$limit === 0)) {
                    break 2;
                }
            }

            yield $poolItems;

            $poolItems = [];
        }

        if ($poolItems) {
            yield $poolItems;
        }
    }


    /**
     * @param (I18nWordInterface|string)[]|null  $andWordsIn
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return I18nPoolItemInterface[]
     */
    public function getWords(
        array $andWordsIn = null,
        array $andGroupsIn = null,
        array $andLangsIn = null,
        //
        int $limit = null,
        int $offset = 0
    ) : array
    {
        $gen = $this->getWordsIt(
            $andWordsIn, $andGroupsIn, $andLangsIn,
            $limit, $offset
        );

        $poolItemsList = [];

        foreach ( $gen as $poolItemsChunk ) {
            $poolItemsList = array_merge(
                $poolItemsList,
                $poolItemsChunk
            );
        }

        return $poolItemsList;
    }

    /**
     * @param (I18nWordInterface|string)[]|null  $andWordsIn
     * @param (I18nGroupInterface|string)[]|null $andGroupsIn
     * @param (I18nLangInterface|string)[]|null  $andLangsIn
     *
     * @return iterable<I18nPoolItemInterface[]>
     */
    public function getWordsIt(
        array $andWordsIn = null,
        array $andGroupsIn = null,
        array $andLangsIn = null,
        //
        int $limit = null,
        int $offset = 0
    ) : iterable
    {
        if (! ($andLangsIn && $andGroupsIn)) {
            // > @gzhegow, file repository cannot select by keys, so the languages and groups is required

            return;
        }

        $andWordsIn = $andWordsIn ?? [];
        $andGroupsIn = $andGroupsIn ?? [];
        $andLangsIn = $andLangsIn ?? [];

        if ($limit < 1) $limit = null;
        if ($offset < 0) $offset = 0;

        $withLimit = (null !== $limit);

        $wordsToFetch = $this->buildWords($andWordsIn, $andGroupsIn);

        $fileSources = $this->buildFileSources($andLangsIn, $andGroupsIn);

        $generator = $this->loadItemsFromFilesIt($fileSources);

        $poolItems = [];
        foreach ( $generator as $data ) {
            $items = $data[ 'items' ];

            $it = (null !== $wordsToFetch)
                ? array_intersect_key($items, $wordsToFetch)
                : $items;

            foreach ( $it as $poolItem ) {
                if ($offset > 0) {
                    $offset--;

                    continue;
                }

                $poolItems[] = $poolItem;

                if ($withLimit && (--$limit === 0)) {
                    break 2;
                }
            }

            yield $poolItems;

            $poolItems = [];
        }

        if ($poolItems) {
            yield $poolItems;
        }
    }


    /**
     * @param I18nPoolItemInterface[] $poolItems
     *
     * @return iterable<bool[]>
     */
    public function save(array $poolItems) : array
    {
        $gen = $this->saveIt($poolItems);

        $reportTotal = [];

        foreach ( $gen as $report ) {
            $reportTotal += $report;
        }

        return $reportTotal;
    }

    /**
     * @param I18nPoolItemInterface[] $poolItems
     *
     * @return bool[]
     */
    public function saveIt(array $poolItems) : iterable
    {
        $poolItemList = [];
        foreach ( $poolItems as $i => $poolItem ) {
            $poolItemList[ $i ] = I18nPoolItem::from($poolItem);
        }

        $fileSources = [];
        $poolItemsByFileSource = [];
        foreach ( $poolItemList as $i => $poolItem ) {
            $poolItemLang = $poolItem->getLang();
            $poolItemGroup = $poolItem->getGroup();

            $fileSource = $this->buildFileSource($poolItemLang, $poolItemGroup);

            $fileSourcePath = $fileSource->getValue();
            $fileSources[ $fileSourcePath ] = $fileSource;

            $poolItemsByFileSource[ $fileSourcePath ][ $i ] = $poolItem;
        }

        if ($fileSources) {
            foreach ( $poolItemsByFileSource as $fileSourcePath => $list ) {
                $fileSource = $fileSources[ $fileSourcePath ];

                $reportCurrent = $this->saveItemsToFile($fileSource, $list);

                yield $reportCurrent;
            }
        }
    }


    /**
     * @param I18nPoolItemInterface[] $poolItems
     *
     * @return bool[]
     */
    public function delete(array $poolItems) : array
    {
        $gen = $this->deleteIt($poolItems);

        $reportTotal = [];

        foreach ( $gen as $report ) {
            $reportTotal += $report;
        }

        return $reportTotal;
    }

    /**
     * @param I18nPoolItemInterface[] $poolItems
     *
     * @return iterable<bool[]>
     */
    public function deleteIt(array $poolItems) : iterable
    {
        $poolItemList = [];
        foreach ( $poolItems as $i => $poolItem ) {
            $poolItemList[ $i ] = I18nPoolItem::from($poolItem);
        }

        $fileSources = [];
        $poolItemsByFileSource = [];
        foreach ( $poolItemList as $i => $poolItem ) {
            $poolItemLang = $poolItem->getLang();
            $poolItemGroup = $poolItem->getGroup();

            $fileSource = $this->buildFileSource($poolItemLang, $poolItemGroup);

            $fileSourcePath = $fileSource->getValue();
            $fileSources[ $fileSourcePath ] = $fileSource;

            $poolItemsByFileSource[ $fileSourcePath ][ $i ] = $poolItem;
        }

        if ($fileSources) {
            foreach ( $poolItemsByFileSource as $fileSourcePath => $list ) {
                $fileSource = $fileSources[ $fileSourcePath ];

                $reportCurrent = $this->deleteItemsFromFile($fileSource, $list);

                yield $reportCurrent;
            }
        }
    }


    /**
     * @return iterable<string, bool>
     */
    public function buildWords(array $words = null, array $groups = null) : ?iterable
    {
        $result = null;

        if (null === $words) {
            return null;
        }

        $groups = $groups ?? [];

        $wordsUnique = array_unique($words);

        $groupsUnique = null;
        foreach ( $wordsUnique as $word ) {
            $parts = explode(I18n::WORD_SEPARATOR, $word, 3);

            if (count($parts) > 2) {
                $result[ $word ] = true;

            } else {
                $groupsUnique = $groupsUnique ?? array_unique($groups);

                foreach ( $groupsUnique as $group ) {
                    $wordWithGroup = implode(I18n::WORD_SEPARATOR, [
                        $group,
                        $word,
                    ]);

                    $result[ $wordWithGroup ] = true;
                }
            }
        }

        if ($result) {
            ksort($result);
        }

        return $result;
    }


    /**
     * @return I18nFileSourceInterface[]
     */
    public function buildFileSources(array $langs, array $groups) : array
    {
        $fileSources = [];

        foreach ( $langs as $lang ) {
            foreach ( $groups as $group ) {
                $fileSource = $this->buildFileSource($lang, $group);
                $fileSourcePath = $fileSource->getValue();

                $fileSources[ $fileSourcePath ] = $fileSource;
            }
        }

        return $fileSources;
    }

    abstract public function buildFileSource(string $lang, string $group) : I18nFileSourceInterface;


    /**
     * @param I18nFileSourceInterface[] $fileSources
     *
     * @return \Traversable<array{
     *     fileSource: I18nFileSourceInterface,
     *     items: array<string, I18nPoolItemInterface>
     * }>
     */
    public function loadItemsFromFilesIt(array $fileSources) : iterable
    {
        if (! $this->isInitialized()) {
            $this->initialize();
        }

        foreach ( $fileSources as $fileSource ) {
            if (null === $fileSource->hasRealpath()) {
                throw new RuntimeException(
                    'File not found: ' . $fileSource->getValue()
                );
            }

            $items = $this->loadItemsFromFile($fileSource);

            yield [
                'fileSource' => $fileSource,
                'items'      => $items,
            ];
        }
    }

    /**
     * @return array<string, I18nPoolItemInterface>
     */
    abstract public function loadItemsFromFile(I18nFileSourceInterface $fileSource) : array;


    /**
     * @param I18nPoolItemInterface[] $poolItems
     *
     * @return bool[]
     */
    public function saveItemsToFile(I18nFileSourceInterface $fileSource, array $poolItems) : array
    {
        if (! $this->isInitialized()) {
            $this->initialize();
        }

        $report = [];

        $fileSourceGroup = $fileSource->getGroup();
        $fileSourcePath = $fileSource->getValue();

        $fileExists = is_file($fileSourcePath);

        $poolItemsCurrent = [];

        if ($fileExists) {
            $poolItemsCurrent = $this->loadItemsFromFile($fileSource);
        }

        if ($poolItems) {
            foreach ( $poolItems as $poolItem ) {
                if (! is_a($poolItem, I18nPoolItemInterface::class)) {
                    throw new LogicException(
                        [
                            'Each of `words` should be `false` or instance of: ' . I18nPoolItemInterface::class,
                            $poolItem,
                        ]
                    );
                }
            }

            $isWrite = false;
            foreach ( $poolItems as $i => $poolItem ) {
                $itemGroup = $poolItem->getGroup();

                if ($itemGroup !== $fileSourceGroup) {
                    $report[ $i ] = false;

                    continue;
                }

                $itemWord = $poolItem->getWord();

                $poolItemsCurrent[ $itemWord ] = $poolItem;

                $report[ $i ] = true;

                $isWrite = true;
            }

            if ($isWrite) {
                $array = [];
                foreach ( $poolItemsCurrent as $word => $poolItem ) {
                    $array[ $word ] = $poolItem->getChoices();
                }

                if ($fileExists) {
                    copy($fileSourcePath, $fileSourcePath . '.backup' . date('Ymd_His_u'));

                } else {
                    $fileSourceDirpath = dirname($fileSourcePath);

                    $directoryExists = file_exists($fileSourceDirpath);

                    if (! $directoryExists) {
                        mkdir($fileSourceDirpath, 0775, true);
                    }
                }

                $this->saveChoicesArrayToFile($fileSource, $array);
            }
        }

        return $report;
    }

    /**
     * @param I18nPoolItemInterface[] $poolItems
     *
     * @return bool[]
     */
    public function deleteItemsFromFile(I18nFileSourceInterface $fileSource, array $poolItems) : array
    {
        if (! $this->isInitialized()) {
            $this->initialize();
        }

        $report = [];

        $fileSourcePath = $fileSource->getValue();

        $fileExists = is_file($fileSourcePath);

        $poolItemsCurrent = [];

        if ($fileExists) {
            $poolItemsCurrent = $this->loadItemsFromFile($fileSource);
        }

        if ($poolItems) {
            foreach ( $poolItems as $poolItem ) {
                if (! is_a($poolItem, I18nPoolItemInterface::class)) {
                    throw new LogicException(
                        [
                            'Each of `words` should be `false` or instance of: ' . I18nPoolItemInterface::class,
                            $poolItem,
                        ]
                    );
                }
            }

            $isWrite = false;
            foreach ( $poolItems as $i => $poolItem ) {
                $itemWord = $poolItem->getWord();

                if (! array_key_exists($itemWord, $poolItemsCurrent)) {
                    $report[ $i ] = false;

                    continue;
                }

                unset($poolItemsCurrent[ $itemWord ]);

                $report[ $i ] = true;

                $isWrite = true;
            }

            if ($isWrite) {
                $choicesArray = [];
                foreach ( $poolItemsCurrent as $word => $poolItem ) {
                    $choicesArray[ $word ] = $poolItem->getChoices();
                }

                if ($fileExists) {
                    copy($fileSourcePath, $fileSourcePath . '.backup' . date('Ymd_His_u'));
                }

                if (! $choicesArray) {
                    if ($fileExists) {
                        unlink($fileSourcePath);
                    }

                } else {
                    if (! $fileExists) {
                        $fileSourceDirpath = dirname($fileSourcePath);

                        $directoryExists = file_exists($fileSourceDirpath);

                        if (! $directoryExists) {
                            mkdir($fileSourceDirpath, 0775, true);
                        }
                    }

                    $this->saveItemsToFile($fileSource, $choicesArray);
                }
            }
        }

        return $report;
    }

    /**
     * @param array<string, string[]> $choicesArray
     */
    abstract public static function saveChoicesArrayToFile(I18nFileSourceInterface $fileSource, array $choicesArray) : bool;
}
