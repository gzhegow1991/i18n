<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace Gzhegow\I18n\Repository\File;

use Gzhegow\I18n\Type\Type;
use Gzhegow\I18n\Pool\PoolItemInterface;
use Gzhegow\I18n\Exception\RuntimeException;
use Gzhegow\I18n\Repository\File\Struct\FileSourceInterface;


class JsonFileRepository extends AbstractFileRepository
{
    public function __construct(string $langDir)
    {
        if (! extension_loaded('json')) {
            throw new RuntimeException(
                [
                    'Extension `ext-json` is required to use this repository',
                    $this,
                ]
            );
        }

        parent::__construct($langDir);
    }


    public function buildFileSource(string $lang, string $group) : FileSourceInterface
    {
        $_lang = Type::theLang($lang);
        $_group = Type::theGroup($group);

        $path = $this->langDir . '/' . $_lang . '/' . $_group . '.json';

        $fileSource = Type::theFileSource([
            'path'  => $path,
            //
            'lang'  => $_lang,
            'group' => $_group,
        ]);

        return $fileSource;
    }


    /**
     * @return array<string, PoolItemInterface>
     */
    public function loadItemsFromFile(FileSourceInterface $fileSource) : array
    {
        $poolItems = [];

        $fileSourceLang = $fileSource->getLang();
        $fileSourceGroup = $fileSource->getGroup();
        $fileSourceRealpath = $fileSource->getRealpath();

        $content = file_get_contents($fileSourceRealpath);

        $choicesArray = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                'Invalid JSON in file / ' . json_last_error_msg() . ': ' . $fileSourceRealpath
            );
        }

        foreach ( $choicesArray as $word => $poolItemChoices ) {
            $poolItemPhrase = $poolItemChoices[ 0 ];
            $poolItemWord = Type::theWord($word);

            $poolItemGroup = $poolItemWord->getGroup();

            if ($poolItemGroup !== $fileSourceGroup) {
                throw new RuntimeException(
                    'Stored `word` has group that is not match with `poolItem` group: '
                    . $poolItemGroup
                    . ' / ' . $fileSourceGroup
                );
            }

            $poolItem = Type::thePoolItem([
                'word'    => $poolItemWord,
                //
                'lang'    => $fileSourceLang,
                //
                'phrase'  => $poolItemPhrase,
                'choices' => $poolItemChoices,
            ]);

            $poolItems[ $word ] = $poolItem;
        }

        return $poolItems;
    }


    /**
     * @param FileSourceInterface     $fileSource
     * @param array<string, string[]> $choicesArray
     *
     * @return bool
     */
    public static function saveChoicesArrayToFile(FileSourceInterface $fileSource, array $choicesArray) : bool
    {
        $fileSourcePath = $fileSource->getValue();

        $content = json_encode($choicesArray);

        $status = file_put_contents($fileSourcePath, $content);

        return $status;
    }
}
