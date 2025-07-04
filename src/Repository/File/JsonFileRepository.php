<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace Gzhegow\I18n\Repository\File;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\I18n\Pool\I18nPoolItem;
use Gzhegow\I18n\Pool\I18nPoolItemInterface;
use Gzhegow\I18n\Exception\RuntimeException;
use Gzhegow\I18n\Repository\File\FileSource\I18nFileSource;
use Gzhegow\I18n\Repository\File\FileSource\I18nFileSourceInterface;


class JsonFileRepository extends AbstractI18nFileRepository
{
    public function __construct(string $langDir)
    {
        Lib::format()->json();

        parent::__construct($langDir);
    }


    public function buildFileSource(string $lang, string $group) : I18nFileSourceInterface
    {
        $langObject = I18nType::lang($lang);
        $groupObject = I18nType::group($group);

        $filepath = ''
            . $this->langDir
            . '/' . $langObject->getValue()
            . '/' . $groupObject->getValue()
            . '.json';

        $fileSource = I18nFileSource::from([
            'filepath' => $filepath,
            //
            'lang'     => $langObject,
            'group'    => $groupObject,
        ]);

        return $fileSource;
    }


    /**
     * @return array<string, I18nPoolItemInterface>
     */
    public function loadItemsFromFile(I18nFileSourceInterface $fileSource) : array
    {
        $theJson = Lib::format()->json();

        $poolItems = [];

        $fileSourceLang = $fileSource->getLang();
        $fileSourceGroup = $fileSource->getGroup();
        $fileSourceRealpath = $fileSource->getRealpath();

        $content = file_get_contents($fileSourceRealpath);

        $choicesArray = $theJson->jsonc_decode($content, true);

        foreach ( $choicesArray as $word => $poolItemChoices ) {
            $poolItemPhrase = $poolItemChoices[ 0 ];
            $poolItemWord = I18nType::word($word);

            $poolItemGroup = $poolItemWord->getGroup();

            if ($poolItemGroup !== $fileSourceGroup) {
                throw new RuntimeException(
                    'Stored `word` has group that is not match with `poolItem` group: '
                    . $poolItemGroup
                    . ' / ' . $fileSourceGroup
                );
            }

            $poolItem = I18nPoolItem::from([
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
     * @param I18nFileSourceInterface $fileSource
     * @param array<string, string[]> $choicesArray
     *
     * @return bool
     */
    public static function saveChoicesArrayToFile(I18nFileSourceInterface $fileSource, array $choicesArray) : bool
    {
        $fileSourcePath = $fileSource->getValue();

        $content = json_encode($choicesArray);

        $status = file_put_contents($fileSourcePath, $content);

        return $status;
    }
}
