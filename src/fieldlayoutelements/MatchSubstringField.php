<?php

namespace codemonauts\glossary\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseNativeField;

class MatchSubstringField extends BaseNativeField
{
    /**
     * @inheritdoc
     */
    public bool $mandatory = true;

    /**
     * @inheritdoc
     */
    public string $attribute = 'matchSubstring';

    /**
     * @var string Label for 'off' status.
     */
    public string $offLabel = 'full word only';

    /**
     * @var string Label for 'on' status.
     */
    public string $onLabel = 'substring';

    /**
     * @var bool Whether the input should get a `disabled` attribute.
     */
    public bool $disabled = false;

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
    public function defaultInstructions(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('glossary', 'Should the term and the synonyms match also as substrings or only as full words?');
    }

    /**
     * @inheritdoc
     */
    protected function inputHtml(ElementInterface $element = null, bool $static = false): ?string
    {
        $this->instructions = Craft::t('glossary', $this->instructions);

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
