<?php

namespace codemonauts\glossary\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\StandardField;

class CaseSensitivityField extends StandardField
{
    /**
     * @inheritdoc
     */
    public $mandatory = true;

    /**
     * @inheritdoc
     */
    public $attribute = 'caseSensitive';

    /**
     * @var string Label for 'on' status.
     */
    public $onLabel = 'sensitive';

    /**
     * @var string Label for 'off' status.
     */
    public $offLabel = 'insensitive';

    /**
     * @var bool Whether the input should get a `disabled` attribute.
     */
    public $disabled = false;

    /**
     * @inheritdoc
     */
    public $tip = 'How the term and synonyms should match when searching.';

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        unset(
            $config['mandatory'],
            $config['attribute'],
            $config['onLabel'],
            $config['offLabel'],
        );

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();

        unset(
            $fields['mandatory'],
            $fields['attribute'],
            $fields['onLabel'],
            $fields['offLabel'],
        );

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function defaultLabel(ElementInterface $element = null, bool $static = false)
    {
        return Craft::t('glossary', 'Case Sensitivity');
    }

    /**
     * @inheritdoc
     */
    protected function statusClass(ElementInterface $element = null, bool $static = false)
    {
        if ($element && ($status = $element->getAttributeStatus('caseSensitivity'))) {
            return $status[0];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function statusLabel(ElementInterface $element = null, bool $static = false)
    {
        if ($element && ($status = $element->getAttributeStatus('caseSensitivity'))) {
            return $status[1];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function inputHtml(ElementInterface $element = null, bool $static = false)
    {
        return Craft::$app->getView()->renderTemplate('_includes/forms/lightswitch', [
            'id' => $this->id(),
            'on' => $this->value($element),
            'name' => $this->name ?? $this->attribute(),
            'disabled' => $static || $this->disabled,
            'onLabel' => $this->onLabel,
            'offLabel' => $this->offLabel,
            'description' => 'Test',
        ]);
    }
}
