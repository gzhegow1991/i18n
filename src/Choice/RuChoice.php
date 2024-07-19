<?php

namespace Gzhegow\I18n\Choice;

use Gzhegow\I18n\Lib;
use Gzhegow\I18n\Exception\LogicException;


class RuChoice implements ChoiceInterface
{
    public function choice($number) : int
    {
        if (null === ($_number = Lib::parse_numeric($number))) {
            throw new LogicException(
                'The `number` should be valid number: ' . Lib::php_dump($number)
            );
        }

        $absNumber = ltrim($_number, '- ');

        $decimals = strlen(
            substr(strrchr($absNumber, '.'), 1)
        );

        $n10 = bcmod($absNumber, 10, $decimals);
        $n100 = bcmod($absNumber, 100, $decimals);

        $isPlural0 = (true
            && 0 === bccomp($n10, 1, $decimals) // n10 = 1
            && 0 !== bccomp($n100, 11, $decimals) // n100 != 11
        );

        $isPlural1 = (false
            || $decimals
            || (true
                && (-1 !== bccomp($n10, 2, $decimals)) // n10 >= 2
                && (1 !== bccomp($n10, 4, $decimals)) // n10 <= 4
                && (false
                    || (-1 === bccomp($n100, 10, $decimals)) // n100 < 10
                    || (-1 !== bccomp($n100, 20, $decimals)) // n100 >= 20
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
