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
        $glossaries = Glossary::getInstance()->getGlossaries();
        $terms = Glossary::getInstance()->getTerms();

        if ($handle !== null) {
            $glossary = $glossaries->getGlossaryByHandle($handle);
            if (!$glossary) {
                Craft::warning('Could not find glossary with handle "' . $handle . '".');

                return $value;
            }
        } else {
            $glossary = $glossaries->getDefaultGlossary();
            if (!$glossary) {
                Craft::warning('Could not find default glossary.');

                return $value;
            }
        }

        $glossary->registerAssets();

        return $terms->renderTerms($value, $glossary);
    }
}
