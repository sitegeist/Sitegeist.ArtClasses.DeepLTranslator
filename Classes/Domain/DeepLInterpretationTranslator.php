<?php

/*
 * This file is part of the Sitegeist.ArtClasses.DeepLTranslator package.
 */

declare(strict_types=1);

namespace Sitegeist\ArtClasses\DeepLTranslator\Domain;

use DeepL\Translator;
use Neos\Flow\I18n\Locale;
use Sitegeist\ArtClasses\Domain\Interpretation\ImageInterpretation;
use Sitegeist\ArtClasses\Domain\Translation\ImageInterpretationTranslatorInterface;

/**
 * An image interpretation translator based on DeepL
 */
final class DeepLInterpretationTranslator implements ImageInterpretationTranslatorInterface
{
    public function __construct(
        private readonly Translator $translator
    ) {
    }

    public function translateImageInterpretation(
        ImageInterpretation $imageInterpretation,
        ?Locale $sourceLocale,
        Locale $targetLocale
    ): ImageInterpretation {
        $translatedValues = $this->translator->translateText(
            [
                $imageInterpretation->description
            ],
            $sourceLocale?->getLanguage(),
            $targetLocale->getLanguage()
        );

        return new ImageInterpretation(
            $targetLocale,
            $translatedValues[0]->text ?? $imageInterpretation->description
        );
    }
}
