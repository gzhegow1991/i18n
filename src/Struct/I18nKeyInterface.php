<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;


interface I18nKeyInterface extends
    ToStringInterface
{
    public function getValue() : string;
}
