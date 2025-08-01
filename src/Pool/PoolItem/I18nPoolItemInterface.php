<?php

namespace Gzhegow\I18n\Pool\PoolItem;

use Gzhegow\I18n\Struct\I18nWordInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;


interface I18nPoolItemInterface extends
    ToArrayInterface
{
    public function toArray(array $options = []) : array;


    public function getLang() : string;

    public function getWord() : I18nWordInterface;


    public function getPhrase() : string;

    /**
     * @return string[]
     */
    public function getChoices() : array;

    public function getChoice(int $n) : string;
}
