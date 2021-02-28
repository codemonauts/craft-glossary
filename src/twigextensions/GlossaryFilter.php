<?php

namespace codemonauts\glossary\twigextensions;

use codemonauts\glossary\Glossary;
use Craft;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class GlossaryFilter extends AbstractExtension
{
    public function getName(): string
    {
        return 'Glossary term replacement';
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('glossary', [$this, 'replace'], ['is_safe' => ['html']]),
        ];
    }

    public function replace($value, $handle = null): string
    {
        if ($handle !== null) {
            $glossary = Glossary::getInstance()->getGlossaries()->getGlossaryByHandle($handle);
            if (!$glossary) {
                Craft::warning('Could not find glossary with handle "' . $handle . '".');

                return $value;
            }
        } else {
            $glossary = Glossary::getInstance()->getGlossaries()->getDefaultGlossary();
            if (!$glossary) {
                Craft::warning('Could not find default glossary.');

                return $value;
            }
        }

        $glossary->registerAssets();

        $provider = $glossary->getProvider();

        return $provider->renderTerms($value, $glossary);
    }
}
