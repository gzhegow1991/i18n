<?php

namespace Gzhegow\I18n\Repo\File\Struct;


interface FileSourceInterface
{
    public function getValue() : string;


    public function getLang() : string;

    public function getGroup() : string;


    public function hasRealpath() : ?string;

    public function getRealpath() : string;
}
