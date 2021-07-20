<?php

namespace codemonauts\glossary\migrations;

use craft\db\Migration;
use craft\db\Table;

class Install extends Migration
{
    public function safeUp()
    {
        $this->dropTableIfExists('{{%glossary_glossaries}}');
        $this->createTable('{{%glossary_glossaries}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'default' => $this->boolean()->notNull(),
            'termTemplate' => $this->string()->null(),
            'contentTemplate' => $this->string()->notNull(),
            'css' => $this->string(),
            'script' => $this->string(),
            'fieldLayoutId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);

        $this->addForeignKey(null, '{{%glossary_glossaries}}', ['id'], Table::ELEMENTS, ['id'], 'CASCADE', null);

        $this->dropTableIfExists('{{%glossary_terms}}');
        $this->createTable('{{%glossary_terms}}', [
            'id' => $this->primaryKey(),
            'term' => $this->string()->notNull(),
            'synonyms' => $this->text(),
            'glossaryId' => $this->integer()->notNull(),
            'caseSensitive' => $this->boolean(),
            'matchSubstring' => $this->boolean(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);

        $this->addForeignKey(null, '{{%glossary_terms}}', ['id'], Table::ELEMENTS, ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%glossary_terms}}', ['glossaryId'], Table::ELEMENTS, ['id'], 'CASCADE', null);
    }

    public function safeDown()
    {
        $this->dropTableIfExists('{{%glossary_glossaries}}');
        $this->dropTableIfExists('{{%glossary_terms}}');
    }
}
