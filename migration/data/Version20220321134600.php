<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220321134600 extends AbstractMigration
{
    //The migration done here creates a new table
    //NOTE: write migrations so that they can be run multiple times without breaking anything.
    //      Means: check if changes are already present before actually creating a table
    public function up(Schema $schema): void
    {
        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        //add module specific table. To be used with a shop model it needs OXID and TIMESTAMP columns.
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
