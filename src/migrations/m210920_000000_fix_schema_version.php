<?php

namespace codemonauts\glossary\migrations;

use craft\db\Migration;

/**
 * m210920_000000_fix_schema_version migration.
 */
class m210920_000000_fix_schema_version extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210920_000000_fix_schema_version cannot be reverted.\n";
        return false;
    }
}
