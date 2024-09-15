<?php

namespace codemonauts\glossary\elements\db;

use codemonauts\glossary\elements\Glossary as GlossaryElement;
use codemonauts\glossary\elements\Term as TermElement;
use codemonauts\glossary\records\Glossary as GlossaryRecord;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

/**
 * Class TermQuery
 *
 * @method TermElement[]|array all($db = null)
 * @method TermElement|null one($db = null)
 */
class TermQuery extends ElementQuery
{
    public $glossaryId;

    /**
     * @param string|string[]|GlossaryElement|null $value The property value
     *
     * @return static self reference
     */
    public function glossary($value): TermQuery
    {
        if ($value instanceof GlossaryElement) {
            // $this->glossaryId = $value->id;
            $this->glossaryId = $value['id'];
        } elseif ($value !== null) {
            $this->glossaryId = GlossaryRecord::find()
                ->select(['id'])
                ->where(DB::parseParam('handle', $value))
                ->column();
        } else {
            $this->glossaryId = null;
        }

        return $this;
    }

    /**
     * @param int|int[]|null $value The property value
     *
     * @return static self reference
     */
    public function glossaryId($value): TermQuery
    {
        $this->glossaryId = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('glossary_terms');

        $this->query->select([
            'glossary_terms.term',
            'glossary_terms.synonyms',
            'glossary_terms.glossaryId',
            'glossary_terms.caseSensitive',
            'glossary_terms.matchSubstring',
        ]);

        if ($this->glossaryId) {
            $this->subQuery->andWhere(Db::parseParam('glossary_terms.glossaryId', $this->glossaryId));
        }

        return parent::beforePrepare();
    }
}
