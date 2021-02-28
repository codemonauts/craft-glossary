<?php

namespace codemonauts\glossary\elements\db;

use codemonauts\glossary\elements\Glossary as GlossaryElement;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

/**
 * Class GlossaryQuery
 *
 * @method GlossaryElement[]|array all($db = null)
 * @method GlossaryElement|null one($db = null)
 */
class GlossaryQuery extends ElementQuery
{
    public $default;
    public $handle;

    /**
     * @param bool $value The property value
     *
     * @return static self reference
     */
    public function default($value): GlossaryQuery
    {
        $this->default = (bool)$value;

        return $this;
    }

    /**
     * @param string $value The property value
     *
     * @return static self reference
     */
    public function handle($value): GlossaryQuery
    {
        $this->handle = (string)$value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('glossary_glossaries');

        $this->query->select([
            'glossary_glossaries.title',
            'glossary_glossaries.handle',
            'glossary_glossaries.provider',
            'glossary_glossaries.default',
            'glossary_glossaries.template',
            'glossary_glossaries.css',
            'glossary_glossaries.script',
            'glossary_glossaries.fieldLayoutId',
        ]);

        if ($this->default !== null) {
            $this->subQuery->andWhere(Db::parseBooleanParam('glossary_glossaries.default', $this->default));
        }

        if ($this->handle !== null) {
            $this->subQuery->andWhere(Db::parseParam('glossary_glossaries.handle', $this->handle));
        }

        return parent::beforePrepare();
    }
}
