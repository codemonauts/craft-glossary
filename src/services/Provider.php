<?php

namespace codemonauts\glossary\services;

use codemonauts\glossary\elements\Glossary;
use codemonauts\glossary\elements\Term;
use yii\base\Component;

abstract class Provider extends Component
{
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * Returns the name of the provider to display.
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Search and replace the terms in a text based on a glossary.
     *
     * @param $text The text so search and replace.
     * @param Glossary $glossary The glossary to use.
     *
     * @return string
     */
    abstract public function renderTerms($text, Glossary $glossary): string;

    /**
     * Returns all terms to search for.
     *
     * @param Term $term
     *
     * @return array
     */
    public function parseTerms(Term $term)
    {
        $terms = [
            $term->term,
        ];

        if ($term->synonyms != '') {
            $synonyms = explode(',', $term->synonyms);
            $terms = array_merge($terms, $synonyms);
        }

        return $terms;
    }
}
