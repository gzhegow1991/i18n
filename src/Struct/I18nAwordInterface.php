<?php

namespace Gzhegow\I18n\Struct;

interface I18nAwordInterface
{
    public function getValue() : string;


    public function hasWord() : ?string;

    public function getWord() : string;


    public function hasGroup() : ?string;

    public function getGroup() : string;


    public function hasPhrase() : ?string;

    public function getPhrase() : string;
}
