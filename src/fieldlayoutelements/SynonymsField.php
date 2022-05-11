<?php

namespace codemonauts\glossary\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\TextField;

class SynonymsField extends TextField
{
    /**
     * @inheritdoc
     */
    public bool $mandatory = true;

    /**
     * @inheritdoc
     */
    public string $attribute = 'synonyms';

    /**
     * @inheritdoc
     */
    public bool $translatable = true;

    /**
     * @inheritdoc
     */
    public bool $required = false;

    /**
     * @inheritdoc
     */
    public function defaultLabel(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('glossary', 'Synonyms');
    }

    /**
     * @inheritdoc
     */
    public function defaultInstructions(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('glossary', 'Comma separated list of Synonyms of the main term to also process.');
    }
}
