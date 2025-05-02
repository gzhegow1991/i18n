<?php

namespace Gzhegow\I18n;

interface I18nAwareInterface
{
    /**
     * @param null|I18nInterface $i18n
     *
     * @return void
     */
    public function setI18n(?I18nInterface $i18n) : void;
}
