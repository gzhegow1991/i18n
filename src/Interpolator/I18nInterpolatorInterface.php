<?php

namespace Gzhegow\I18n\Interpolator;

interface I18nInterpolatorInterface
{
    public function interpolate(?string $phrase, ?array $placeholders = null) : ?string;
}
