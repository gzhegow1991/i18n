<?php

namespace Gzhegow\I18n\Pool;


interface I18nPoolItemInterface
{
    public function getWord() : string;


    public function getLang() : string;

    public function getGroup() : string;


    public function getPhrase() : string;


    public function getChoice(int $n) : string;

    /**
     * @return string[]
     */
    public function getChoices() : array;
}
