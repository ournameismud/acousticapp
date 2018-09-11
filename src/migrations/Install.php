<?php
/**
 * Acoustic App plugin for Craft CMS 3.x
 *
 * Acoustic App @asclearasmud
 *
 * @link      http://ournameismud.co.uk/
 * @copyright Copyright (c) 2018 @asclearasmud
 */

namespace ournameismud\acousticapp\migrations;

use ournameismud\acousticapp\AcousticApp;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * @author    @asclearasmud
 * @package   AcousticApp
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

   /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%acousticapp_favourites}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%acousticapp_favourites}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'siteId' => $this->integer()->notNull(),
                    'userId' => $this->integer()->notNull(),
                    'testId' => $this->integer()->notNull(),
                ]
            );
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%acousticapp_testsSeals}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%acousticapp_testsSeals}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'siteId' => $this->integer()->notNull(),
                    'testId' => $this->integer()->notNull(),
                    'sealId' => $this->integer()->notNull(),
                    'context' => $this->string(255)->notNull(),
                    'quantity' => $this->integer()->notNull(),
                ]
            );
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%acousticapp_tests}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%acousticapp_tests}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'siteId' => $this->integer()->notNull(),
                    'dB' => $this->integer()->notNull(),
                    'fireRating' => $this->string(255)->notNull(),
                    'manufacturer' => $this->string(255)->defaultValue(''),
                    'blankName' => $this->string(255)->defaultValue(''),
                    'intRef' => $this->string(255)->defaultValue(''),
                    'doorThickness' => $this->integer()->notNull(),
                    'doorset' => $this->string(255)->defaultValue(''),
                    'glassType' => $this->string(255)->defaultValue(''),
                    'lorientId' => $this->integer()->notNull(),
                    'testDate' => $this->dateTime(),
                ]
            );

            // $this->addForeignKey(
            //     $this->db->getForeignKeyName('{{%acousticapp_tests}}', 'id'),
            //     '{{%acousticapp_tests}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%acousticapp_seals}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%acousticapp_seals}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'siteId' => $this->integer()->notNull(),
                    'sealCode' => $this->string(255)->notNull(),
                    'lorientId' => $this->integer()->notNull(),
                    'craftId' => $this->integer()->notNull(),
                ]
            );

            // $this->addForeignKey(
            //     $this->db->getForeignKeyName('{{%acousticapp_seals}}', 'id'),
            //     '{{%acousticapp_seals}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%acousticapp_searches}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%acousticapp_searches}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'siteId' => $this->integer()->notNull(),
                    'hash' => $this->string(255)->notNull()->defaultValue(''),
                    'criteria' => $this->text()->notNull()->defaultValue(''),
                    'userId' => $this->integer(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(
            $this->db->getIndexName(
                '{{%acousticapp_favourites}}',
                'id',
                true
            ),
            '{{%acousticapp_favourites}}',
            'id',
            true
        );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }

        $this->createIndex(
            $this->db->getIndexName(
                '{{%acousticapp_testsSeals}}',
                'id',
                true
            ),
            '{{%acousticapp_testsSeals}}',
            ['id'],
            true
        );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }

        $this->createIndex(
            $this->db->getIndexName(
                '{{%acousticapp_tests}}',
                'id',
                true
            ),
            '{{%acousticapp_tests}}',
            ['id','lorientId'],
            true
        );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }

        $this->createIndex(
            $this->db->getIndexName(
                '{{%acousticapp_seals}}',
                'id',
                true
            ),
            '{{%acousticapp_seals}}',
            ['id','sealCode'],
            true
        );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }

        $this->createIndex(
            $this->db->getIndexName(
                '{{%acousticapp_searches}}',
                'id',
                true
            ),
            '{{%acousticapp_searches}}',
            'id',
            true
        );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%acousticapp_favourites}}', 'siteId'),
            '{{%acousticapp_favourites}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%acousticapp_tests}}', 'siteId'),
            '{{%acousticapp_tests}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%acousticapp_seals}}', 'siteId'),
            '{{%acousticapp_seals}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%acousticapp_searches}}', 'siteId'),
            '{{%acousticapp_searches}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists('{{%acousticapp_favourites}}');

        $this->dropTableIfExists('{{%acousticapp_tests}}');

        $this->dropTableIfExists('{{%acousticapp_seals}}');

        $this->dropTableIfExists('{{%acousticapp_searches}}');
    }
}
