<?php

namespace codemonauts\glossary\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\StandardTextField;

class TermField extends StandardTextField
{
    /**
     * @inheritdoc
     */
    public $mandatory = true;

    /**
     * @inheritdoc
     */
    public $attribute = 'term';

    /**
     * @inheritdoc
     */
    public $translatable = true;

    /**
     * @inheritdoc
     */
    public $maxlength = 255;

    /**
     * @inheritdoc
     */
    public $required = true;

    /**
     * @inheritdoc
     */
    public $autofocus = true;

    /**
     * @inheritdoc
     */
    public $instructions = 'The main term to process.';

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        unset(
            $config['mandatory'],
            $config['attribute'],
            $config['translatable'],
            $config['maxlength'],
            $config['required'],
            $config['autofocus']
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
            $fields['maxlength'],
            $fields['required'],
            $fields['autofocus']
        );

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function defaultLabel(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('glossary', 'Term');
    }

    /**
     * @inheritdoc
     */
    protected function statusClass(ElementInterface $element = null, bool $static = false)
    {
        if ($element && ($status = $element->getAttributeStatus('term'))) {
            return $status[0];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function statusLabel(ElementInterface $element = null, bool $static = false)
    {
        if ($element && ($status = $element->getAttributeStatus('term'))) {
            return $status[1];
        }

        return null;
    }
}
