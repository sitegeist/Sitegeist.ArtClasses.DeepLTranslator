<?php

/*
 * This file is part of the Sitegeist.ArtClasses.DeepLTranslator package.
 */

declare(strict_types=1);

namespace Sitegeist\ArtClasses\DeepLTranslator\Domain;

use DeepL\Translator;
use Neos\Flow\Annotations as Flow;

/**
 * The factory for creating DeepLInterpretationTranslators from configuration
 */
#[Flow\Scope('singleton')]
final class DeepLInterpretationTranslatorFactory
{
    public function create(string $authKey): DeepLInterpretationTranslator
    {
        return new DeepLInterpretationTranslator(new Translator($authKey));
    }
}
