<?php

namespace codemonauts\glossary\records;

use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\records\Element;
use yii\db\ActiveQueryInterface;

/**
 * Class Glossary
 *
 * @property int $id ID
 * @property string $title The title of the glossary.
 * @property string $handle The handle for the glossary.
 * @property string $provider The class of the provider.
 * @property bool $default If this is the default glossary to use.
 * @property string $template The template to use for rendering the terms.
 * @property string $css A CSS path to load when a glossary has been used on the page.
 * @property string $script A script path to load when a glossary has been used on the page.
 * @property int $fieldLayoutId The ID of the field layout.
 * @property Element $element Element
 */
class Glossary extends ActiveRecord
{
    use SoftDeleteTrait;

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%glossary_glossaries}}';
    }

    /**
     * Returns the element.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }
}
