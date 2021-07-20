<?php

namespace codemonauts\glossary\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\StandardField;

class MatchSubstringField extends StandardField
{
    /**
     * @inheritdoc
     */
    public $mandatory = true;

    /**
     * @inheritdoc
     */
    public $attribute = 'matchSubstring';

    /**
     * @var string Label for 'off' status.
     */
    public $offLabel = 'full word only';

    /**
     * @var string Label for 'on' status.
     */
    public $onLabel = 'substring';

    /**
     * @var bool Whether the input should get a `disabled` attribute.
     */
    public $disabled = false;

    /**
     * @inheritdoc
     */
    public $instructions = 'Should the term and the synonyms match also as substrings or only as full words?';

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
    public function fields(): array
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
    public function defaultLabel(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('glossary', 'Match as substring?');
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
    protected function inputHtml(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::$app->getView()->renderTemplate('_includes/forms/lightswitch', [
            'id' => $this->id(),
            'on' => $this->value($element),
            'name' => $this->name ?? $this->attribute(),
            'disabled' => $static || $this->disabled,
            'onLabel' => $this->onLabel,
            'offLabel' => $this->offLabel,
        ]);
    }
}
