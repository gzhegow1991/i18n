<?php

namespace Gzhegow\I18n\Choice;

use Gzhegow\Lib\Lib;


class I18nRuChoice implements I18nChoiceInterface
{
    public function choice($number) : int
    {
        $theType = Lib::type();

        $numberValid = $theType->number($number, false)->orThrow();
        $numberValidAbs = $numberValid->getValueAbsolute();
        $numberValidScale = $numberValid->getScale();

        $n10 = bcmod($numberValidAbs, 10, $numberValidScale);
        $n100 = bcmod($numberValidAbs, 100, $numberValidScale);

        $isPlural0 = (true
            && 0 === bccomp($n10, 1, $numberValidScale) // n10 = 1
            && 0 !== bccomp($n100, 11, $numberValidScale) // n100 != 11
        );

        $isPlural1 = (false
            || $numberValidScale
            || (true
                && (-1 !== bccomp($n10, 2, $numberValidScale)) // n10 >= 2
                && (1 !== bccomp($n10, 4, $numberValidScale)) // n10 <= 4
                && (false
                    || (-1 === bccomp($n100, 10, $numberValidScale)) // n100 < 10
                    || (-1 !== bccomp($n100, 20, $numberValidScale)) // n100 >= 20
                )
            )
        );

        $n = null
            ?? ($isPlural0 ? 0 : null)
            ?? ($isPlural1 ? 1 : null)
            ?? 2;

        return $n;
    }
}
