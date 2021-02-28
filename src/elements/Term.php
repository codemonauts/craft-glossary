<?php

namespace codemonauts\glossary\elements;

use codemonauts\glossary\elements\db\TermQuery;
use codemonauts\glossary\records\Term as TermRecord;
use codemonauts\glossary\Glossary as GlossaryPlugin;
use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\models\FieldLayout;
use yii\base\Exception;

/**
 * Class Term
 */
class Term extends Element
{
    /**
     * @var string The term to match.
     */
    public $term;

    /**
     * @var string Synonyms to match.
     */
    public $synonyms;

    /**
     * @var int The glossary ID the term is associated with.
     */
    public $glossaryId;

    /**
     * @var bool Should the term and synonyms match case sensitive?
     */
    public $caseSensitive;

    /**
     * @var bool Match as substring?
     */
    public $matchSubstring;

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
        return 'Term';
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
        return 'Terms';
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
    public function getIsEditable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl(): string
    {
        return 'glossary/term/' . $this->id;
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
                'label' => 'All terms',
                'criteria' => [],
            ],
            [
                'heading' => 'Glossaries',
            ],
        ];

        $glossaries = GlossaryPlugin::getInstance()->getGlossaries()->getAllGlossaries();
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
            'term' => 'Term',
            'caseSensitive' => 'Case Sensitive',
            'matchSubstring' => 'Match as substring',
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
            'term' => 'Term',
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
    public function tableAttributeHtml(string $attribute): string
    {
        if ($attribute === 'caseSensitive') {
            return $this->caseSensitive ? '<div data-icon="check" aria-label="' . Craft::t('app', 'Yes') . '""></div>' : '';
        }

        if ($attribute === 'matchSubstring') {
            return $this->matchSubstring ? '<div data-icon="check" aria-label="' . Craft::t('app', 'Yes') . '""></div>' : '';
        }

        return parent::tableAttributeHtml($attribute);
    }

    /**
     * @inheritDoc
     */
    protected static function defineFieldLayouts(string $source): array
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

        return $glossary !== null ? $glossary->getFieldLayout() : null;
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

        $record->term = (string)$this->term;
        $record->synonyms = (string)$this->synonyms;
        $record->glossaryId = (int)$this->glossaryId;
        $record->caseSensitive = (bool)$this->caseSensitive;
        $record->matchSubstring = (bool)$this->matchSubstring;

        $record->save(false);

        parent::afterSave($isNew);
    }

    /**
     * @inheritDoc
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['term'], 'required'];
        $rules[] = [['glossaryId'], 'integer'];
        $rules[] = [['caseSensitive', 'matchSubstring'], 'boolean'];

        return $rules;
    }
}
