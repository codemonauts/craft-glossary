<?php

namespace codemonauts\glossary\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\StandardTextField;

class SynonymsField extends StandardTextField
{
    /**
     * @inheritdoc
     */
    public $mandatory = true;

    /**
     * @inheritdoc
     */
    public $attribute = 'synonyms';

    /**
     * @inheritdoc
     */
    public $translatable = true;

    /**
     * @inheritdoc
     */
    public $required = false;

    /**
     * @inheritdoc
     */
    public $instructions = 'Comma separated list of Synonyms of the main term to also process.';

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        unset(
            $config['mandatory'],
            $config['attribute'],
            $config['translatable'],
            $config['required'],
        );

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function fields(): array
    {
        $fields = parent::fields();

        unset(
            $fields['mandatory'],
            $fields['attribute'],
            $fields['translatable'],
            $fields['required'],
        );

        return $fields;
    }

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
    protected function statusClass(ElementInterface $element = null, bool $static = false)
    {
        if ($element && ($status = $element->getAttributeStatus('synonyms'))) {
            return $status[0];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function statusLabel(ElementInterface $element = null, bool $static = false)
    {
        if ($element && ($status = $element->getAttributeStatus('synonyms'))) {
            return $status[1];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function inputHtml(ElementInterface $element = null, bool $static = false)
    {
        $this->instructions = Craft::t('glossary', $this->instructions);

        return parent::inputHtml($element, $static);
    }
}
