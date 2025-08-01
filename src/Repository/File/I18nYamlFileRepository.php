<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace Gzhegow\I18n\Repository\File;

use Gzhegow\I18n\Type\I18nType;
use Gzhegow\I18n\Exception\RuntimeException;
use Gzhegow\I18n\Pool\PoolItem\I18nPoolItemInterface;
use Gzhegow\I18n\Repository\File\FileSource\I18nFileSource;
use Gzhegow\I18n\Repository\File\FileSource\I18nFileSourceInterface;


class I18nYamlFileRepository extends AbstractI18nFileRepository
{
    public function __construct(string $langDir)
    {
        if (! extension_loaded('yaml')) {
            throw new RuntimeException(
                [
                    'Extension `ext-yaml` is required to use this repository',
                    $this,
                ]
            );
        }

        parent::__construct($langDir);
    }


    public function buildFileSource(string $lang, string $group) : I18nFileSourceInterface
    {
        $langObject = I18nType::lang($lang);
        $groupObject = I18nType::group($group);

        $langString = $langObject->getValue();
        $groupString = $groupObject->getValue();

        $filepath = $langString . '/' . $groupString;

        $fileSource = [
            'filepath' => $this->langDir . '/' . $filepath . '.yaml',
            //
            'lang'     => $langObject,
            'group'    => $groupObject,
        ];

        $fileSource = I18nFileSource::fromArray($fileSource)->orThrow();

        return $fileSource;
    }

    /**
     * @return array<string, I18nPoolItemInterface>
     */
    public function loadPoolItemsFromFile(I18nFileSourceInterface $fileSource) : array
    {
        $poolItems = [];

        $fileSourceLang = $fileSource->getLang();
        $fileSourceGroup = $fileSource->getGroup();
        $fileSourceRealpath = $fileSource->getRealpath();

        $choicesArray = yaml_parse_file($fileSourceRealpath);

        foreach ( $choicesArray as $word => $poolItemChoices ) {
            $poolItemPhrase = $poolItemChoices[ 0 ];
            $poolItemWord = I18nType::word($word);

            $poolItemGroup = $poolItemWord->getGroup();

            if ($poolItemGroup !== $fileSourceGroup) {
                throw new RuntimeException(
                    [
                        'Stored `word` has group that is not match with `poolItem` group',
                        //
                        $poolItemGroup,
                        $fileSourceGroup,
                    ]
                );
            }

            $poolItem = [
                'word'    => $poolItemWord,
                //
                'lang'    => $fileSourceLang,
                //
                'phrase'  => $poolItemPhrase,
                'choices' => $poolItemChoices,
            ];

            $poolItem = I18nType::poolItem($poolItem);

            $poolItems[ $word ] = $poolItem;
        }

        return $poolItems;
    }

    /**
     * @param array<string, string[]> $choicesArray
     */
    public function saveChoicesArrayToFile(I18nFileSourceInterface $fileSource, array $choicesArray) : bool
    {
        $fileSourcePath = $fileSource->getValue();

        $status = yaml_emit_file($fileSourcePath, $choicesArray);

        return $status;
    }
}
