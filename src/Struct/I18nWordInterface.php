<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;


interface I18nWordInterface extends
    ToArrayInterface,
    ToStringInterface
{
    public function getValue() : string;


    public function getGroup() : string;

    public function getSection() : string;

    public function getKey() : string;
}
