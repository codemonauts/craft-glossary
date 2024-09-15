<?php

namespace codemonauts\glossary\elements;

use codemonauts\glossary\elements\db\TermQuery;
use codemonauts\glossary\elements\Glossary as GlossaryElement;
use codemonauts\glossary\records\Term as TermRecord;
use codemonauts\glossary\Glossary as GlossaryPlugin;
use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\Cp;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use yii\base\Exception;

class Term extends Element
{
    /**
     * @var string The term to match.
     */
    public string $term = '';

    /**
     * @var string|null Synonyms to match.
     */
    public ?string $synonyms = null;

    /**
     * @var int|null The glossary ID the term is associated with.
     */
    public ?int $glossaryId = null;

    /**
     * @var bool Should the term and synonyms match case sensitive?
     */
    public bool $caseSensitive = false;

    /**
     * @var bool Match as substring?
     */
    public bool $matchSubstring = false;

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->term;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('glossary', 'Term');
    }

    /**
     * @inheritdoc
     */
    public static function lowerDisplayName(): string
    {
        return 'term';
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('glossary', 'Terms');
    }

    /**
     * @inheritdoc
     */
    public static function pluralLowerDisplayName(): string
    {
        return 'terms';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function cpEditUrl(): string
    {
        return UrlHelper::cpUrl('glossary/term/' . $this->canonicalId);
    }

    /**
     * @inheritDoc
     * @return TermQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new TermQuery(static::class);
    }

    /**
     * @inheritDoc
     */
    public static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('glossary', 'All terms'),
                'criteria' => [],
            ],
            [
                'heading' => Craft::t('glossary', 'Glossaries'),
            ],
        ];

        $glossaries = GlossaryPlugin::$plugin->getGlossaries()->getAllGlossaries();
        foreach ($glossaries as $glossary) {
            $sources[] = [
                'key' => $glossary->handle,
                'label' => $glossary->title,
                'criteria' => [
                    'glossaryId' => $glossary->id,
                ],
            ];
        }

        return $sources;
    }

    /**
     * @inheritDoc
     */
    public static function defineTableAttributes(): array
    {
        return [
            'term' => Craft::t('glossary', 'Term'),
            'caseSensitive' => Craft::t('glossary', 'Case Sensitive'),
            'matchSubstring' => Craft::t('glossary', 'Match as substring'),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'term',
            'caseSensitive',
            'matchSubstring',
        ];
    }

    /**
     * @inheritDoc
     */
    public static function defineSortOptions(): array
    {
        return [
            'term' => Craft::t('glossary', 'Term'),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function defineSearchableAttributes(): array
    {
        return [
            'term',
            'synonyms',
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeHtml(string $attribute): string
    {
        if ($attribute === 'caseSensitive') {
            return $this->caseSensitive ? '<div data-icon="check" aria-label="' . Craft::t('app', 'Yes') . '""></div>' : '';
        }

        if ($attribute === 'matchSubstring') {
            return $this->matchSubstring ? '<div data-icon="check" aria-label="' . Craft::t('app', 'Yes') . '""></div>' : '';
        }

        return parent::attributeHtml($attribute);
    }

    /**
     * @inheritDoc
     */
    protected static function defineFieldLayouts(?string $source): array
    {
        if ($source === '*') {
            $glossaries = GlossaryPlugin::getInstance()->getGlossaries()->getAllGlossaries();
        } else {
            $glossary = GlossaryPlugin::getInstance()->getGlossaries()->getGlossaryByHandle($source);
            $glossaries = [$glossary];
        }

        $fieldLayouts = [];
        foreach ($glossaries as $glossary) {
            $fieldLayouts[] = $glossary->getFieldLayout();
        }

        return $fieldLayouts;
    }

    /**
     * @inheritDoc
     */
    public function getFieldLayout(): ?FieldLayout
    {
        if ($this->glossaryId) {
            $glossary = Glossary::findOne(['id' => $this->glossaryId]);
        } else {
            $glossary = Glossary::findOne(['default' => true]);
        }

        if (!$glossary) {
            $glossary = Glossary::findOne();
        }

        return $glossary?->getFieldLayout();
    }

    /**
     * @inheritDoc
     */
    public function afterSave(bool $isNew): void
    {
        if (!$isNew) {
            $record = TermRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid term ID: ' . $this->id);
            }
        } else {
            $record = new TermRecord();
            $record->id = (int)$this->id;
        }

        $record->term = $this->term;
        $record->synonyms = $this->synonyms;
        $record->glossaryId = $this->glossaryId;
        $record->caseSensitive = $this->caseSensitive;
        $record->matchSubstring = $this->matchSubstring;

        $record->save(false);

        parent::afterSave($isNew);
    }

    /**
     * @inheritDoc
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['term'], 'required', 'on' => self::SCENARIO_LIVE];
        $rules[] = [['term', 'synonyms'], 'trim'];
        $rules[] = [['glossaryId'], 'integer'];
        $rules[] = [['caseSensitive', 'matchSubstring'], 'boolean'];

        return $rules;
    }

    /**
     * @inerhitdoc
     */
    public function canView(User $user): bool
    {
        return $user->can('glossary:termEdit');
    }

    /**
     * @inerhitdoc
     */
    public function canSave(User $user): bool
    {
        return $user->can('glossary:termEdit');
    }

    /**
     * @inerhitdoc
     */
    public function canDelete(User $user): bool
    {
        return $user->can('glossary:termEdit');
    }

    /**
     * @inheritdoc
     */
    public function canCreateDrafts(User $user): bool
    {
        return $user->can('glossary:termEdit');
    }

    /**
     * @inheritdoc
     */
    protected function metaFieldsHtml(bool $static): string
    {
        $fields = [];

        $options = [];
        $glossaries = GlossaryElement::findAll();
        foreach ($glossaries as $glossary) {
            $options[] = [
                'label' => Craft::t('site', $glossary->title),
                'value' => $glossary->id,
            ];
        }

        if (!$static) {
            $view = Craft::$app->getView();
            $glossaryInputId = $view->namespaceInputId('glossary');
            $js = <<<EOD
(() => {
    const \$typeInput = $('#$glossaryInputId');
    const editor = \$typeInput.closest('form').data('elementEditor');
    if (editor) {
        editor.checkForm();
    }
})();
EOD;
            $view->registerJs($js);
        }

        $fields[] = Cp::selectFieldHtml([
            'label' => Craft::t('glossary', 'Glossary'),
            'id' => 'glossary',
            'name' => 'glossaryId',
            'value' => $this->glossaryId,
            'options' => $options,
            'disabled' => $static,
        ]);

        $fields[] = parent::metaFieldsHtml($static);

        return implode("\n", $fields);
    }

    /**
     * @inheritdoc
     */
    public function getPostEditUrl(): ?string
    {
        return UrlHelper::cpUrl("glossary/terms");
    }
}
