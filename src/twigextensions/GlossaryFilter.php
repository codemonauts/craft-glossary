<?php

namespace codemonauts\glossary\twigextensions;

use codemonauts\glossary\Glossary;
use Craft;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use yii\web\View;

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

        /**
        * @warning The original return statement was commented out because it only returned the processed text
        * without injecting the rendered glossary term tooltips into the HTML output.
        *
        * To ensure that the generated tooltip elements (e.g., <div id="term-...">) are included in the final
        * page markup, we now explicitly register them using `registerHtml()` at the end of the page.
        *
        * This change is required to support dynamic tooltips rendered outside the content flow.
        **/
        //return $terms->renderTerms($value, $glossary);

        $result = $terms->renderTerms($value, $glossary);
        Craft::$app->view->registerHtml($terms->getRenderedTerms(), View::POS_END);

        return $result;

    }
}
