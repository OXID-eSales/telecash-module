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
final class Version20241018134600 extends AbstractMigration
{
    //The migration done here creates a new table
    //NOTE: write migrations so that they can be run multiple times without breaking anything.
    //      Means: check if changes are already present before actually creating a table
    public function up(Schema $schema): void
    {
        $this->platform->registerDoctrineTypeMapping('enum', 'string');

        //add payment-extension-table
        $this->createPaymentTeleCashTable($schema);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }

    /**
     * create a telecash payment-extend-table
     * @throws SchemaException
     */
    private function createPaymentTeleCashTable(Schema $schema): void
    {
        if (!$schema->hasTable(Module::TELECASH_PAYMENT_EXTENSION_TABLE)) {
            $paymentTable = $schema->createTable(Module::TELECASH_PAYMENT_EXTENSION_TABLE);
        } else {
            $paymentTable = $schema->getTable(Module::TELECASH_PAYMENT_EXTENSION_TABLE);
        }

        if (!$paymentTable->hasColumn('OXID')) {
            $paymentTable->addColumn(
                'OXID',
                Types::STRING,
                ['columnDefinition' => 'char(32) collate latin1_general_ci']
            );
        }

        if (!$paymentTable->hasColumn('OXSHOPID')) {
            $paymentTable->addColumn(
                'OXSHOPID',
                Types::INTEGER,
                [
                    'columnDefinition' => 'int(11)',
                    'default' => 1,
                    'comment' => 'Shop ID (oxshops)'
                ]
            );
        }

        $oxPaymentIdColName = strtoupper(Module::TELECASH_PAYMENT_EXTENSION_TABLE_OXPAYMENTID);
        if (!$paymentTable->hasColumn($oxPaymentIdColName)) {
            $paymentTable->addColumn(
                $oxPaymentIdColName,
                Types::STRING,
                [
                    'columnDefinition' => 'char(32) collate latin1_general_ci',
                    'comment' => 'OXID Payment id (oxpayment)'
                ]
            );
        }

        $oxIdentColName = strtoupper(Module::TELECASH_PAYMENT_EXTENSION_TABLE_IDENT);
        if (!$paymentTable->hasColumn($oxIdentColName)) {
            $paymentTable->addColumn(
                $oxIdentColName,
                Types::STRING,
                [
                    'columnDefinition' => sprintf(
                        "ENUM('%s') COLLATE 'latin1_general_ci' NOT NULL DEFAULT '%s'",
                        implode("','", Module::TELECASH_PAYMENT_IDENTS),
                        Module::TELECASH_PAYMENT_IDENT_DEFAULT
                    ),
                    'comment' => 'ident for TeleCash-Payment. The default is '
                        . Module::TELECASH_PAYMENT_IDENT_DEFAULT
                ]
            );
        }

        $oxCaptureTypeColName = strtoupper(Module::TELECASH_PAYMENT_EXTENSION_TABLE_CAPTURETYPE);
        if (!$paymentTable->hasColumn($oxCaptureTypeColName)) {
            // For the column definition we use the credit card types,
            // as they represent the maximum of possible variants.
            $paymentTable->addColumn(
                $oxCaptureTypeColName,
                Types::STRING,
                [
                    'columnDefinition' => sprintf(
                        "ENUM('%s') COLLATE 'latin1_general_ci' NOT NULL DEFAULT '%s'",
                        implode("','", Module::TELECASH_CAPTURE_TYPES[Module::TELECASH_PAYMENT_IDENT_CREDITCARD]),
                        Module::TELECASH_CAPTURE_TYPE_DIRECT
                    ),
                    'comment' => 'Capture-Type for TeleCash-Payment. The default is none'
                        . Module::TELECASH_CAPTURE_TYPE_DIRECT
                ]
            );
        }

        if (!$paymentTable->hasColumn('OXTIMESTAMP')) {
            $paymentTable->addColumn(
                'OXTIMESTAMP',
                Types::DATETIME_MUTABLE,
                ['columnDefinition' => 'timestamp default current_timestamp on update current_timestamp']
            );
        }

        if (!$paymentTable->hasPrimaryKey()) {
            $paymentTable->setPrimaryKey(['OXID']);
        }

        if (!$paymentTable->hasIndex('UNIQUE_ENTRY')) {
            $paymentTable->addUniqueIndex(
                ['OXSHOPID', $oxPaymentIdColName, $oxIdentColName, $oxCaptureTypeColName],
                'UNIQUE_ENTRY'
            );
        }
    }
}
