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
final class Version20241129122600 extends AbstractMigration
{
    //The migration done here creates a new table
    //NOTE: write migrations so that they can be run multiple times without breaking anything.
    //      Means: check if changes are already present before actually creating a table
    public function up(Schema $schema): void
    {
        $this->platform->registerDoctrineTypeMapping('enum', 'string');

        //add order-extension-table
        $this->createTeleCashOrderTable($schema);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }

    /**
     * create a telecash order-extend-table
     * @throws SchemaException
     */
    private function createTeleCashOrderTable(Schema $schema): void
    {
        if (!$schema->hasTable(Module::TELECASH_ORDER_EXTENSION_TABLE)) {
            $teleCashOrderTable = $schema->createTable(Module::TELECASH_ORDER_EXTENSION_TABLE);
        } else {
            $teleCashOrderTable = $schema->getTable(Module::TELECASH_ORDER_EXTENSION_TABLE);
        }

        $oxIdColName = 'OXID';
        if (!$teleCashOrderTable->hasColumn($oxIdColName)) {
            $teleCashOrderTable->addColumn(
                $oxIdColName,
                Types::STRING,
                ['columnDefinition' => 'char(32) collate latin1_general_ci']
            );
        }

        $oxOrderIdColName = strtoupper(Module::TELECASH_ORDER_EXTENSION_TABLE_OXORDERID);
        if (!$teleCashOrderTable->hasColumn($oxOrderIdColName)) {
            $teleCashOrderTable->addColumn(
                $oxOrderIdColName,
                Types::STRING,
                [
                    'columnDefinition' => 'char(32) collate latin1_general_ci',
                    'comment' => 'OXID Order id (oxorder)'
                ]
            );
        }

        $oxOidColName = strtoupper(Module::TELECASH_ORDER_EXTENSION_TABLE_OID);
        if (!$teleCashOrderTable->hasColumn($oxOidColName)) {
            $teleCashOrderTable->addColumn(
                $oxOidColName,
                Types::STRING,
                [
                    'columnDefinition' => 'char(40) collate latin1_general_ci',
                    'comment' => 'Telecash OID'
                ]
            );
        }

        $oxTxnTypeColName = strtoupper(Module::TELECASH_ORDER_EXTENSION_TABLE_TXNTYPE);
        if (!$teleCashOrderTable->hasColumn($oxTxnTypeColName)) {
            $teleCashOrderTable->addColumn(
                $oxTxnTypeColName,
                Types::STRING,
                [
                    'columnDefinition' => 'char(8) collate latin1_general_ci',
                    'comment' => 'Telecash TXNType'
                ]
            );
        }

        $oxPaymentMethodColName = strtoupper(Module::TELECASH_ORDER_EXTENSION_TABLE_PAYMENTMETHOD);
        if (!$teleCashOrderTable->hasColumn($oxPaymentMethodColName)) {
            $teleCashOrderTable->addColumn(
                $oxPaymentMethodColName,
                Types::STRING,
                [
                    'columnDefinition' => 'char(16) collate latin1_general_ci',
                    'comment' => 'Telecash PaymentMethod'
                ]
            );
        }

        $oxStatusColName = strtoupper(Module::TELECASH_ORDER_EXTENSION_TABLE_STATUS);
        if (!$teleCashOrderTable->hasColumn($oxStatusColName)) {
            $teleCashOrderTable->addColumn(
                $oxStatusColName,
                Types::STRING,
                [
                    'columnDefinition' => 'char(32) collate latin1_general_ci',
                    'comment' => 'Telecash Status'
                ]
            );
        }

        $oxCurrencyColName = strtoupper(Module::TELECASH_ORDER_EXTENSION_TABLE_CURRENCY);
        if (!$teleCashOrderTable->hasColumn($oxCurrencyColName)) {
            $teleCashOrderTable->addColumn(
                $oxCurrencyColName,
                Types::STRING,
                [
                    'columnDefinition' => 'char(3) collate latin1_general_ci',
                    'comment' => 'Telecash Currency'
                ]
            );
        }

        $oxChargeTotalColName = strtoupper(Module::TELECASH_ORDER_EXTENSION_TABLE_CHARGETOTAL);
        if (!$teleCashOrderTable->hasColumn($oxChargeTotalColName)) {
            $teleCashOrderTable->addColumn(
                $oxChargeTotalColName,
                Types::FLOAT,
                [
                    'columnDefinition' => 'DOUBLE NOT NULL DEFAULT 0',
                    'comment' => 'Telecash Charge Total'
                ]
            );
        }

        if (!$teleCashOrderTable->hasColumn('OXTIMESTAMP')) {
            $teleCashOrderTable->addColumn(
                'OXTIMESTAMP',
                Types::DATETIME_MUTABLE,
                ['columnDefinition' => 'timestamp default current_timestamp on update current_timestamp']
            );
        }

        if (!$teleCashOrderTable->hasPrimaryKey()) {
            $teleCashOrderTable->setPrimaryKey(['OXID']);
        }

        if (!$teleCashOrderTable->hasIndex('UNIQUE_ENTRY')) {
            $teleCashOrderTable->addUniqueIndex(
                [$oxOrderIdColName, $oxOidColName],
                'UNIQUE_ENTRY'
            );
        }
    }
}
