<?php

namespace Gzhegow\I18n\Choice;

use Gzhegow\Lib\Lib;


class I18nCallableChoice implements I18nChoiceInterface
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
        $theType = Lib::type();

        $numberValid = $theType->number($number, false)->orThrow();

        $n = call_user_func($this->fn, $numberValid);

        return $n;
    }
}
