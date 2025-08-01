<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;


interface I18nAwordInterface extends
    ToArrayInterface,
    ToStringInterface
{
    public function getValue() : string;


    public function getWordOrPhrase() : string;


    /**
     * @param I18nWordInterface $refWord
     */
    public function isWord(&$refWord = null) : bool;

    public function getWord() : I18nWordInterface;


    /**
     * @param string $refPhrase
     */
    public function isPhrase(&$refPhrase = null) : bool;

    public function getPhrase() : string;
}
