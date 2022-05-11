<?php

namespace codemonauts\glossary\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseNativeField;

class CaseSensitivityField extends BaseNativeField
{
    /**
     * @inheritdoc
     */
    public bool $mandatory = true;

    /**
     * @inheritdoc
     */
    public string $attribute = 'caseSensitive';

    /**
     * @var string Label for 'on' status.
     */
    public string $onLabel = 'sensitive';

    /**
     * @var string Label for 'off' status.
     */
    public string $offLabel = 'insensitive';

    /**
     * @var bool Whether the input should get a `disabled` attribute.
     */
    public bool $disabled = false;

    /**
     * @inheritdoc
     */
    public function defaultLabel(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('glossary', 'Case Sensitivity');
    }

    /**
     * @inheritdoc
     */
    public function defaultInstructions(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('glossary', 'How the term and synonyms should match when searching.');
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
            'instructions' => $this->instructions(),
            'onLabel' => Craft::t('glossary', $this->onLabel),
            'offLabel' => Craft::t('glossary', $this->offLabel),
        ]);
    }
}
