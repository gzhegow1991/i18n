<?php

namespace Gzhegow\I18n\Struct;

interface I18nWordInterface
{
    public function getValue() : string;


    public function getGroup() : string;
}
