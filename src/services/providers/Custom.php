<?php

namespace codemonauts\glossary\services\providers;

use codemonauts\glossary\elements\Glossary;
use codemonauts\glossary\elements\Term;
use codemonauts\glossary\services\Provider;
use Craft;
use Exception;
use function Symfony\Component\String\s;

class Custom extends Provider
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'Custom Content';
    }

    /**
     * @inheritDoc
     */
    public function renderTerms($text, Glossary $glossary): string
    {
        $view = Craft::$app->getView();
        $originalText = $text;

        try {
            $template = $glossary->template;
            $replacements = [];
            $terms = Term::find()->glossary($glossary)->all();
            $globalIndex0 = 0;

            foreach ($terms as $term) {
                $index0 = 0;
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
                    $text = s($text)->replaceMatches($pattern, function($matches) use ($term, $template, &$replacements, &$index0, &$globalIndex0, $view) {
                        $replacement = trim($view->renderTemplate($template, [
                            'term' => $term,
                            'text' => $matches[0],
                            'termIndex0' => $index0,
                            'termIndex' => ($index0 + 1),
                            'globalIndex0' => $globalIndex0,
                            'globalIndex' => ($globalIndex0 + 1),
                        ], 'site'));

                        if ($replacement === false) {
                            return $term;
                        }

                        $token = $term->uid . '-' . $index0;
                        $replacements[$token] = $replacement;
                        $index0++;
                        $globalIndex0++;

                        return '{{%' . $token . '%}}';
                    }, $text);
                }
            }

            foreach ($replacements as $token => $replacement) {
                $text = s($text)->replace('{{%' . $token . '%}}', $replacement);
            }
        } catch (Exception $e) {
            Craft::error('Error when rendering glossary terms: ' . $e->getMessage(), 'glossary');
            $text = $originalText;
        }

        return $text;
    }
}
