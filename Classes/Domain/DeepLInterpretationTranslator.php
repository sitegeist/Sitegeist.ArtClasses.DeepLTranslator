<?php

/*
 * This file is part of the Sitegeist.ArtClasses.DeepLTranslator package.
 */

declare(strict_types=1);

namespace Sitegeist\ArtClasses\DeepLTranslator\Domain;

use DeepL\Translator;
use Neos\Flow\I18n\Locale;
use Sitegeist\ArtClasses\Domain\Interpretation\ImageInterpretation;
use Sitegeist\ArtClasses\Domain\Interpretation\InterpretedObject;
use Sitegeist\ArtClasses\Domain\Interpretation\InterpretedText;
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
        $textsToTranslate = [];
        $translationIndices = [];
        $i = 0;
        if ($imageInterpretation->description) {
            $textsToTranslate[] = $imageInterpretation->description;
            $translationIndices['description'] = $i;
            $i++;
        }
        foreach ($imageInterpretation->labels as $interpretedLabel) {
            $textsToTranslate[$i] = $interpretedLabel;
            $translationIndices['labels'][] = $i;
            $i++;
        }
        foreach ($imageInterpretation->objects as $interpretedObject) {
            $textsToTranslate[$i] = $interpretedObject->name;
            $translationIndices['objects'][] = $i;
            $i++;
        }
        foreach ($imageInterpretation->texts as $interpretedText) {
            $textsToTranslate[$i] = $interpretedText->text;
            $translationIndices['texts'][] = $i;
            $i++;
        }

        $translatedValues = $this->translator->translateText(
            $textsToTranslate,
            $sourceLocale?->getLanguage(),
            $targetLocale->getLanguage()
        );

        $description = array_key_exists('description', $translationIndices)
            ? $translatedValues[$translationIndices['description']]
            : $imageInterpretation->description;

        $labels = $imageInterpretation->labels;
        foreach ($translationIndices['labels'] ?? [] as $labelIndex => $translationIndex) {
            $labels[$labelIndex] = $translatedValues[$translationIndex]->text;
        }

        $objects = $imageInterpretation->objects;
        foreach ($translationIndices['objects'] ?? [] as $labelIndex => $translationIndex) {
            $object = $objects[$labelIndex];
            $objects[$labelIndex] = new InterpretedObject(
                $translatedValues[$translationIndex]->text,
                $object->boundingPolygon
            );
        }

        $texts = $imageInterpretation->texts;
        $additionalTexts = [];
        foreach ($translationIndices['texts'] ?? [] as $labelIndex => $translationIndex) {
            $text = $objects[$labelIndex];
            $additionalTexts[] = new InterpretedText(
                $translatedValues[$translationIndex]->text,
                null,
                $text->boundingPolygon
            );
        }

        return new ImageInterpretation(
            $targetLocale,
            $description,
            $labels,
            $objects,
            array_merge(
                $texts,
                $additionalTexts
            ),
            $imageInterpretation->dominantColors,
            $imageInterpretation->cropHints
        );
    }
}
