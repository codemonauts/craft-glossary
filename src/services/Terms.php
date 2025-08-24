<?php

namespace codemonauts\glossary\services;

use codemonauts\glossary\elements\Glossary;
use codemonauts\glossary\elements\Term;
use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Html;
use Exception;
use Twig\Environment as TwigEnvironment;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use yii\base\Component;
use function Symfony\Component\String\s;

class Terms extends Component
{
    protected string $renderedTerms = '';

    protected array $usedTerms = [];

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

        if (!empty($term->synonyms)) {
            $synonyms = explode(',', $term->synonyms);
            $terms = array_merge($terms, $synonyms);
        }

        return ArrayHelper::filterEmptyStringsFromArray($terms);
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
            $termTemplate = !empty($glossary->termTemplate) ? $glossary->termTemplate : '<span>{{ text }}</span>';
            $replacements = [];
            $terms = Term::find()->glossary($glossary)->all();

            foreach ($terms as $term) {
                $template = Html::modifyTagAttributes($termTemplate, [
                    'class' => 'glossary',
                    'data-glossary-term' => 'term-' . $term->id,
                ]);

                $index = 0;
                $words = $this->parseTerms($term);

                foreach ($words as $word) {
                    $word = preg_quote($word, '/');
                    if ($term->matchSubstring) {
                        $pattern = '/' . $word . '/';
                    } else {
                        $pattern = "/\b" . $word . "\b/";
                    }
                    if (!$term->caseSensitive) {
                        $pattern .= 'i';
                    }
                    $text = s($text)->replaceMatches($pattern, function ($matches) use ($term, $template, &$replacements, &$index, $view, $glossary) {
                        try {
                            /**
                             * @warning
                             * $view->renderString(...) internally triggers the instantiation of Twig\Node\Node,
                             * which is deprecated since Twig 3.15 and will become abstract in Twig 4.0.
                             *
                             * This results in a runtime warning or exception in environments that enforce deprecation policies,
                             * particularly when Symfony's String component is used with deprecation handling enabled.
                             *
                             * Exception thrown:
                             * Symfony\Component\String\Exception\InvalidArgumentException:
                             * "Since twig/twig 3.15: Instantiating 'Twig\Node\Node' directly is deprecated; the class will become abstract in 4.0"
                             *
                             * Workaround:
                             * Perform a whitespace-insensitive placeholder substitution for all variables inside the template string.
                             * Supports {{ text }}, {{ term }}, and {{ term.fieldName }} syntax manually, without relying on Twig rendering.*
                             *
                             * This avoids triggering deprecated internal behavior and ensures future compatibility with Twig 4.
                             *
                             * @see https://github.com/twigphp/Twig/releases/tag/v3.15.0
                             * @see https://github.com/codemonauts/craft-glossary/issues/13
                             * @see https://github.com/codemonauts/craft-glossary/issues/13
                             */
                            /*
                            $replacement = trim($view->renderString($template, [
                                'term' => $term,
                                'text' => $matches[0],
                            ], 'site'));
                            */

                            $variables = $term->getFieldValues();
                            $variables['term'] = $term;
                            $variables['text'] = $matches[0];

                            $replacement = preg_replace_callback('/\{\{\s*(.*?)\s*\}\}/',
                                function ($match) use ($variables, $term) {
                                    $key = $match[1];

                                    if ($key === 'text') {
                                        return htmlspecialchars($variables['text'] ?? '',
                                            ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                                    }

                                    if ($key === 'term') {
                                        return htmlspecialchars((string)$term, ENT_QUOTES | ENT_SUBSTITUTE,
                                            'UTF-8');
                                    }

                                    if (str_starts_with($key, 'term.')) {
                                        $field = substr($key, 5);
                                        if (array_key_exists($field, $variables)) {
                                            return htmlspecialchars((string)$variables[$field],
                                                ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                                        }
                                    }

                                    // Fallback: Unverändert zurückgeben
                                    return $match[0];
                                }, $template);
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
                        $variables['text'] = $matches[0];

                        try {
                            /**
                             * @warning
                             * $view->renderTemplate(...) internally triggers the instantiation of Twig\Node\Node,
                             * which is deprecated since Twig 3.15 and will become abstract in Twig 4.0.
                             *
                             * This results in a runtime warning or exception in environments that enforce deprecation policies,
                             * particularly when Symfony's String component is used with deprecation handling enabled.
                             *
                             * Exception thrown:
                             * Symfony\Component\String\Exception\InvalidArgumentException:
                             * "Since twig/twig 3.15: Instantiating 'Twig\Node\Node' directly is deprecated; the class will become abstract in 4.0"
                             *
                             * Workaround:
                             * Instead of using Craft's native view rendering (which relies on Craft's Twig environment with custom node visitors),
                             * we manually:
                             *  1. Load the template file as a string via file_get_contents()
                             *  2. Register it into a Twig\ArrayLoader under a key (e.g. 'tooltip')
                             *  3. Instantiate a clean Twig\Environment with no Craft-specific visitors
                             *  4. Render the template manually using $twig->render('tooltip', [...])
                             *
                             * This avoids triggering deprecated internal behavior and ensures future compatibility with Twig 4.
                             *
                             * @see https://github.com/twigphp/Twig/releases/tag/v3.15.0
                             * @see https://github.com/codemonauts/craft-glossary/issues/13
                             * @see https://github.com/codemonauts/craft-glossary/issues/13
                             */
                            //$this->usedTerms[$term->id] = $view->renderTemplate($glossary->tooltipTemplate, $variables, 'site');

                            $templateString = file_get_contents(Craft::getAlias('@templates/' . $glossary->tooltipTemplate . '.twig'));

                            $loader = new ArrayLoader(['tooltip' => $templateString]);
                            $twig = new TwigEnvironment($loader, [
                                'cache' => false,
                                'autoescape' => 'html',
                            ]);

                            $rendered = $twig->render('tooltip', $variables);

                            $this->usedTerms[$term->id] = $rendered;
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
            foreach ($this->usedTerms as $id => $usedTerm) {
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