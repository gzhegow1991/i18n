<?php

namespace Gzhegow\I18n\Choice;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Exception\LogicException;


class DefaultChoice implements ChoiceInterface
{
    public function choice($number) : int
    {
        if (null === ($_number = Lib::parse_numeric($number))) {
            throw new LogicException(
                [
                    'The `number` should be valid number',
                    $number,
                ]
            );
        }

        $absNumber = ltrim($_number, '- ');

        $decimals = strlen(
            substr(strrchr($absNumber, '.'), 1)
        );

        $isPlural1 = (false
            || $decimals
            || (0 !== bccomp($absNumber, 1, $decimals))
        );

        $n = null
            ?? ($isPlural1 ? 1 : null)
            ?? 0;

        return $n;
    }
}
