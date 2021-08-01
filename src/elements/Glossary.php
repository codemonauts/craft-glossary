<?php

namespace codemonauts\glossary\elements;

use codemonauts\glossary\elements\db\GlossaryQuery;
use codemonauts\glossary\Glossary as GlossaryPlugin;
use codemonauts\glossary\records\Glossary as GlossaryRecord;
use codemonauts\glossary\resources\GlossaryFrontend;
use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Db;
use craft\models\FieldLayout;
use yii\base\Exception;

/**
 * Class Glossary
 */
class Glossary extends Element
{
    public $title;
    public $handle;
    public $default;
    public $termTemplate;
    public $tooltipTemplate;
    public $css;
    public $script;
    public $fieldLayoutId;

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('glossary', 'Glossary');
    }

    /**
     * @inheritdoc
     */
    public static function lowerDisplayName(): string
    {
        return 'glossary';
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('glossary', 'Glossaries');
    }

    /**
     * @inheritdoc
     */
    public static function pluralLowerDisplayName(): string
    {
        return 'glossaries';
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
        return 'glossary/glossary/' . $this->id;
    }

    /**
     * @inheritDoc
     * @return GlossaryQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new GlossaryQuery(static::class);
    }

    /**
     * @inheritDoc
     */
    public static function defineSources(string $context = null): array
    {
        return [
            [
                'key' => '*',
                'label' => Craft::t('glossary', 'All glossaries'),
                'criteria' => [],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function defineTableAttributes(): array
    {
        return [
            'title' => Craft::t('app', 'Title'),
            'handle' => Craft::t('app', 'Handle'),
            'default' => Craft::t('glossary', 'Default'),
            'termTemplate' => Craft::t('glossary', 'Term template'),
            'tooltipTemplate' => Craft::t('glossary', 'Tooltip template'),
            'css' => 'CSS',
            'script' => 'Script',
            'counter' => Craft::t('glossary', '# of Terms'),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'title',
            'handle',
            'default',
            'counter',
        ];
    }

    /**
     * @inheritDoc
     */
    public static function defineSortOptions(): array
    {
        return [
            'title' => 'Title',
            'handle' => 'Handle',
        ];
    }

    /**
     * @inheritDoc
     */
    public static function defineSearchableAttributes(): array
    {
        return [
            'title',
            'handle',
        ];
    }

    /**
     * @inheritDoc
     */
    protected static function defineFieldLayouts(string $source): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getFieldLayout(): ?FieldLayout
    {
        $fieldLayout = parent::getFieldLayout();

        return $fieldLayout ?? new FieldLayout(['type' => self::class]);
    }

    /**
     * @inheritDoc
     */
    public function tableAttributeHtml(string $attribute): string
    {
        if ($attribute === 'default') {
            return $this->default ? '<div data-icon="check" aria-label="' . Craft::t('app', 'Yes') . '""></div>' : '';
        }

        if ($attribute === 'counter') {
            return Term::find()->glossaryId($this->id)->count();
        }

        return parent::tableAttributeHtml($attribute);
    }

    /**
     * @inheritDoc
     */
    public function afterSave(bool $isNew): void
    {
        if (!$isNew) {
            $record = GlossaryRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid glossary ID: ' . $this->id);
            }
        } else {
            $record = new GlossaryRecord();
            $record->id = (int)$this->id;
        }

        $record->title = (string)$this->title;
        $record->handle = (string)$this->handle;
        $record->default = (bool)$this->default;
        $record->termTemplate = $this->termTemplate;
        $record->tooltipTemplate = $this->tooltipTemplate;
        $record->css = $this->css;
        $record->script = $this->script;
        $record->fieldLayoutId = (int)$this->fieldLayoutId;

        $record->save(false);

        // Update old default glossary
        if ($this->default) {
            $oldDefault = GlossaryPlugin::getInstance()->glossaries->getDefaultGlossary()->id;
            if ($oldDefault !== null && $this->id !== $oldDefault) {
                Db::update('{{%glossary_glossaries}}', [
                    'default' => false,
                ], [
                    'id' => $oldDefault,
                ]);
            }
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritDoc
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['title', 'handle', 'default', 'tooltipTemplate'], 'required'];

        return $rules;
    }

    /**
     * Registers the CSS and JS files if set.
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function registerAssets(): void
    {
        $view = Craft::$app->getView();

        $view->registerAssetBundle(GlossaryFrontend::class);

        if ($this->css !== '') {
            $view->registerCssFile(Craft::parseEnv($this->css));
        }

        if ($this->script !== '') {
            $view->registerJsFile(Craft::parseEnv($this->script));
        }
    }

    /**
     * @inheritDoc
     */
    public function afterDelete(): void
    {
        parent::afterDelete();

        // Delete all terms as well
        $terms = Term::findAll(['glossaryId' => $this->id]);
        foreach ($terms as $term) {
            Craft::$app->getElements()->deleteElement($term);
        }
    }
}
