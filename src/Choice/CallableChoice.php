<?php

namespace Gzhegow\I18n\Choice;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Exception\LogicException;


class CallableChoice implements I18nChoiceInterface
{
    /**
     * @var callable
     */
    protected $fn;


    public function __construct(callable $fn)
    {
        $this->fn = $fn;
    }


    public function choice($number) : int
    {
        if (null === ($_number = Lib::parse()->numeric($number))) {
            throw new LogicException(
                [
                    'The `number` should be valid number',
                    $number,
                ]
            );
        }

        $n = call_user_func($this->fn, $_number);

        return $n;
    }
}
