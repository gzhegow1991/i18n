<?php

namespace Gzhegow\I18n\Choice;

interface I18nChoiceInterface
{
    public function choice($number) : int;
}
