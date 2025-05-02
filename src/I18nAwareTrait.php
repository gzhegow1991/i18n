<?php

namespace Gzhegow\I18n;

trait I18nAwareTrait
{
    /**
     * @var I18nFacade
     */
    protected $i18n;


    /**
     * @param null|I18nInterface $i18n
     *
     * @return void
     */
    public function setI18n(?I18nInterface $i18n) : void
    {
        $this->i18n = $i18n;
    }
}
