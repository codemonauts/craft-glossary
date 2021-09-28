<?php

namespace codemonauts\glossary\services;

use codemonauts\glossary\elements\Glossary;
use codemonauts\glossary\elements\Term;
use Craft;
use craft\helpers\Html;
use Exception;
use Twig\Error\SyntaxError;
use yii\base\Component;
use function Symfony\Component\String\s;

class Terms extends Component
{
    protected $renderedTerms = '';

    /**
     * Returns all terms to search for.
     *
     * @param Term $term
     *
     * @return array
     */
    public function parseTerms(Term $term): array
    {
        $terms = [
            $term->term,
        ];

        if ($term->synonyms !== '') {
            $synonyms = explode(',', $term->synonyms);
            $terms = array_merge($terms, $synonyms);
        }

        return $terms;
    }

    /**
     * Search and replace the terms in a text based on a glossary.
     *
     * @param string $text The text so search and replace.
     * @param Glossary $glossary The glossary to use.
     *
     * @return string
     */
    public function renderTerms(string $text, Glossary $glossary): string
    {
        $view = Craft::$app->getView();
        $originalText = $text;

        try {
            $termTemplate = $glossary->termTemplate !== '' ? $glossary->termTemplate : '<span>{{ text }}</span>';
            $replacements = [];
            $terms = Term::find()->glossary($glossary)->all();
            $usedTerms = [];

            foreach ($terms as $term) {
                $template = Html::modifyTagAttributes($termTemplate, [
                    'class' => 'glossary',
                    'data-glossary-term' => 'term-' . $term->id,
                ]);

                $index = 0;
                $words = $this->parseTerms($term);

                foreach ($words as $word) {
                    if ($term->matchSubstring) {
                        $pattern = '/' . $word . '/';
                    } else {
                        $pattern = "/\b" . $word . "\b/";
                    }
                    if (!$term->caseSensitive) {
                        $pattern .= 'i';
                    }
                    $text = s($text)->replaceMatches($pattern, function ($matches) use ($term, $template, &$replacements, &$index, $view, &$usedTerms, $glossary) {
                        try {
                            $replacement = trim($view->renderString($template, [
                                'term' => $term,
                                'text' => $matches[0],
                            ], 'site'));
                        } catch (SyntaxError $e) {
                            Craft::error($e->getMessage(), 'glossary');
                            $replacement = false;
                        }

                        if ($replacement === false) {
                            return $term;
                        }

                        $token = $term->uid . '-' . $index++;
                        $replacements[$token] = $replacement;

                        /**
                         * @deprecated Remove field values with version 2.0 and only use term to access all fields.
                         */
                        $variables = $term->getFieldValues();
                        $variables['term'] = $term;

                        try {
                            $usedTerms[$term->id] = $view->renderTemplate($glossary->tooltipTemplate, $variables, 'site');
                        } catch (SyntaxError $e) {
                            Craft::error($e->getMessage(), 'glossary');
                        }

                        return '{{%' . $token . '%}}';
                    });
                }
            }

            foreach ($replacements as $token => $replacement) {
                $text = s($text)->replace('{{%' . $token . '%}}', $replacement);
            }

            $renderedTerms = '';
            foreach ($usedTerms as $id => $usedTerm) {
                $renderedTerms .= Html::tag('div', $usedTerm, [
                    'id' => 'term-' . $id,
                ]);
            }

            $this->renderedTerms = Html::tag('div', $renderedTerms, [
                'id' => 'glossary-terms',
                'style' => 'display: none;',
            ]);

        } catch (Exception $e) {
            Craft::error('Error when rendering glossary terms: ' . $e->getMessage(), 'glossary');
            $text = $originalText;
        }

        return $text;
    }

    /**
     * Returns the rendered terms
     *
     * @return string
     */
    public function getRenderedTerms(): string
    {
        return $this->renderedTerms;
    }
}