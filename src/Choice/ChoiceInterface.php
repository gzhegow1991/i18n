<?php

namespace Gzhegow\I18n\Choice;

interface ChoiceInterface
{
    public function choice($number) : int;
}
