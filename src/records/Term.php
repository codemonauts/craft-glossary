<?php

namespace codemonauts\glossary\records;

use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\records\Element;
use yii\db\ActiveQueryInterface;

/**
 * Class Term
 *
 * @property int $id ID
 * @property string $term The term.
 * @property string $synonyms Synonyms of the term.
 * @property int $glossaryId The ID of the field layout.
 * @property bool $caseSensitive Match the term and synonyms case-sensitive.
 * @property bool $matchSubstring Match as substring.
 * @property Element $element Element
 */
class Term extends ActiveRecord
{
    use SoftDeleteTrait;

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%glossary_terms}}';
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

    /**
     * Returns the glossary.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getGlossary(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'glossaryId']);
    }
}
