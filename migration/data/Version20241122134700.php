<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use OxidSolutionCatalysts\TeleCash\Core\Module;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241122134700 extends AbstractMigration
{
    //The migration done here creates a new table
    //NOTE: write migrations so that they can be run multiple times without breaking anything.
    //      Means: check if changes are already present before actually creating a table
    public function up(Schema $schema): void
    {
        $this->platform->registerDoctrineTypeMapping('enum', 'string');

        //add order-extension-table
        $this->createTeleCashOrderHistoryTable($schema);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }

    /**
     * create a telecash order-extend-table
     * @throws SchemaException
     */
    private function createTeleCashOrderHistoryTable(Schema $schema): void
    {
        if (!$schema->hasTable(Module::TELECASH_ORDER_HISTORY_EXTENSION_TABLE)) {
            $teleCashOrderHistoryTable = $schema->createTable(Module::TELECASH_ORDER_HISTORY_EXTENSION_TABLE);
        } else {
            $teleCashOrderHistoryTable = $schema->getTable(Module::TELECASH_ORDER_HISTORY_EXTENSION_TABLE);
        }

        if (!$teleCashOrderHistoryTable->hasColumn('OXID')) {
            $teleCashOrderHistoryTable->addColumn(
                'OXID',
                Types::STRING,
                ['columnDefinition' => 'char(32) collate latin1_general_ci']
            );
        }

        $oxOidColName = strtoupper(Module::TELECASH_ORDER_HISTORY_EXTENSION_TABLE_OID);
        if (!$teleCashOrderHistoryTable->hasColumn($oxOidColName)) {
            $teleCashOrderHistoryTable->addColumn(
                $oxOidColName,
                Types::STRING,
                [
                    'columnDefinition' => 'char(40) collate latin1_general_ci',
                    'comment' => 'Telecash OID'
                ]
            );
        }


        $oxResponseColName = strtoupper(Module::TELECASH_ORDER_HISTORY_EXTENSION_TABLE_RESPONSE);
        if (!$teleCashOrderHistoryTable->hasColumn($oxResponseColName)) {
            $teleCashOrderHistoryTable->addColumn(
                $oxResponseColName,
                Types::STRING,
                [
                    'columnDefinition' => 'longtext collate latin1_general_ci',
                    'comment' => 'full response in case something special is needed'
                ]
            );
        }

        if (!$teleCashOrderHistoryTable->hasColumn('OXTIMESTAMP')) {
            $teleCashOrderHistoryTable->addColumn(
                'OXTIMESTAMP',
                Types::DATETIME_MUTABLE,
                ['columnDefinition' => 'timestamp default current_timestamp on update current_timestamp']
            );
        }

        if (!$teleCashOrderHistoryTable->hasPrimaryKey()) {
            $teleCashOrderHistoryTable->setPrimaryKey(['OXID']);
        }

        if (!$teleCashOrderHistoryTable->hasIndex('INDEX_ENTRY')) {
            $teleCashOrderHistoryTable->addIndex(
                [$oxOidColName],
                'INDEX_ENTRY'
            );
        }
    }
}
