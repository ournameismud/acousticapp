<?php

namespace ournameismud\acousticapp\migrations;

use Craft;
use craft\db\Migration;

/**
 * m181127_163205_reportAdd migration.
 */
class m181127_163205_reportAdd extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Place migration code here...
        $this->addColumn('{{%acousticapp_tests}}', 'report', 'int');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181127_163205_reportAdd cannot be reverted.\n";
        return false;
    }
}
