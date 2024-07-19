<?php

namespace Gzhegow\I18n\Repo\File;

use Gzhegow\I18n\Lib;
use Gzhegow\I18n\I18n;
use Gzhegow\I18n\Type\Type;
use Gzhegow\I18n\Repo\RepoInterface;
use Gzhegow\I18n\Struct\WordInterface;
use Gzhegow\I18n\Struct\LangInterface;
use Gzhegow\I18n\Struct\GroupInterface;
use Gzhegow\I18n\Pool\PoolItemInterface;
use Gzhegow\I18n\Exception\LogicException;
use Gzhegow\I18n\Exception\RuntimeException;
use Gzhegow\I18n\Repo\File\Struct\FileSourceInterface;


abstract class AbstractFileRepo implements RepoInterface
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
    ) : array
    {
        if (! ($andLangsIn && $andGroupsIn)) {
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
     * @param (WordInterface|string)[]|null  $andWordsIn
     * @param (GroupInterface|string)[]|null $andGroupsIn
     * @param (LangInterface|string)[]|null  $andLangsIn
     *
     * @return array{
     *     status: bool,
     *     word: WordInterface,
     *     group: GroupInterface,
     *     lang: LangInterface
     * }[]
     */
    public function hasWords(
        array $andWordsIn = null,
        array $andGroupsIn = null,
        array $andLangsIn = null
    ) : array
    {
        if (! ($andWordsIn && $andGroupsIn && $andLangsIn)) {
            // > @gzhegow, file repository cannot select by keys, so the languages and groups is required

            return [];
        }

        $report = [];

        $andWordsIn = $andWordsIn ?? [];
        $andGroupsIn = $andGroupsIn ?? [];
        $andLangsIn = $andLangsIn ?? [];

        $wordsToFetch = $this->buildWords($andWordsIn, $andGroupsIn);

        $fileSources = $this->buildFileSources($andLangsIn, $andGroupsIn);

        $generator = $this->loadItemsFromFiles($fileSources);

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

        $generator = $this->loadItemsFromFiles($fileSources);

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

        $generator = $this->loadItemsFromFiles($fileSources);

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
     * @param PoolItemInterface[] $poolItems
     *
     * @return iterable<bool[]>
     */
    public function save(array $poolItems) : iterable
    {
        $_poolItems = Type::thePoolItemList($poolItems);

        $fileSources = [];
        $poolItemsByFileSource = [];
        foreach ( $_poolItems as $i => $poolItem ) {
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
     * @param PoolItemInterface[] $poolItems
     *
     * @return iterable<bool[]>
     */
    public function delete(array $poolItems) : iterable
    {
        $_poolItems = Type::thePoolItemList($poolItems);

        $fileSources = [];
        $poolItemsByFileSource = [];
        foreach ( $_poolItems as $i => $poolItem ) {
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

        if (null === $words) return null;

        $groups = $groups ?? [];

        $wordsUnique = array_unique($words ?? []);

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
     * @return FileSourceInterface[]
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

    abstract public function buildFileSource(string $lang, string $group) : FileSourceInterface;


    /**
     * @param FileSourceInterface[] $fileSources
     *
     * @return \Traversable<array{
     *     fileSource: FileSourceInterface,
     *     items: array<string, PoolItemInterface>
     * }>
     */
    public function loadItemsFromFiles(array $fileSources) : iterable
    {
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
     * @return array<string, PoolItemInterface>
     */
    abstract public function loadItemsFromFile(FileSourceInterface $fileSource) : array;


    /**
     * @param PoolItemInterface[] $poolItems
     *
     * @return bool[]
     */
    public function saveItemsToFile(FileSourceInterface $fileSource, array $poolItems) : array
    {
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
                if (! is_a($poolItem, PoolItemInterface::class)) {
                    throw new LogicException(
                        'Each of `words` should be `false` or instance of: ' . PoolItemInterface::class
                        . ' / ' . Lib::php_dump($poolItem)
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
     * @param PoolItemInterface[] $poolItems
     *
     * @return bool[]
     */
    public function deleteItemsFromFile(FileSourceInterface $fileSource, array $poolItems) : array
    {
        $report = [];

        $fileSourcePath = $fileSource->getValue();

        $fileExists = is_file($fileSourcePath);

        $poolItemsCurrent = [];

        if ($fileExists) {
            $poolItemsCurrent = $this->loadItemsFromFile($fileSource);
        }

        if ($poolItems) {
            foreach ( $poolItems as $poolItem ) {
                if (! is_a($poolItem, PoolItemInterface::class)) {
                    throw new LogicException(
                        'Each of `words` should be `false` or instance of: ' . PoolItemInterface::class
                        . ' / ' . Lib::php_dump($poolItem)
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
    abstract public static function saveChoicesArrayToFile(FileSourceInterface $fileSource, array $choicesArray) : bool;
}
