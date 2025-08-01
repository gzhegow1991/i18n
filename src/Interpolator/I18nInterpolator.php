<?php

namespace Gzhegow\I18n\Interpolator;

use Gzhegow\I18n\I18n;


class I18nInterpolator implements I18nInterpolatorInterface
{
    public function interpolate(?string $phrase, ?array $placeholders = null) : ?string
    {
        $placeholders = $placeholders ?? [];

        if (null === $phrase) {
            return null;
        }

        $replacements = [];
        foreach ( $placeholders as $variable => $replacement ) {
            $replacementKey = ''
                . I18n::PLACEHOLDER_BRACES[ 0 ]
                . $variable
                . I18n::PLACEHOLDER_BRACES[ 1 ];

            $replacements[ $replacementKey ] = $replacement;
        }

        $phraseInterpolated = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $phrase
        );

        return $phraseInterpolated;
    }
}
