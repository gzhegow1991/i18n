<?php

namespace Gzhegow\I18n\Choice;

use Gzhegow\Lib\Lib;


class I18nDefaultChoice implements I18nChoiceInterface
{
    public function choice($number) : int
    {
        $theType = Lib::type();

        $numberValid = $theType->number($number, false)->orThrow();
        $numberValidAbs = $numberValid->getValueAbsolute();
        $numberValidScale = $numberValid->getScale();

        $isPlural1 = (false
            || $numberValidScale
            || (0 !== bccomp($numberValidAbs, 1, $numberValidScale))
        );

        $n = null
            ?? ($isPlural1 ? 1 : null)
            ?? 0;

        return $n;
    }
}
