<?php

namespace Gzhegow\I18n\Repository\File\FileSource;


interface I18nFileSourceInterface
{
    public function getValue() : string;


    public function getLang() : string;

    public function getGroup() : string;


    public function hasRealpath() : ?string;

    public function getRealpath() : string;
}
