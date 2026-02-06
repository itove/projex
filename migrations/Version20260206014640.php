<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206014640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename table project_base to project';
    }

    public function up(Schema $schema): void
    {
        // Rename table from project_base to project (preserves all data)
        $this->addSql('ALTER TABLE project_base RENAME TO project');
    }

    public function down(Schema $schema): void
    {
        // Rename table back from project to project_base
        $this->addSql('ALTER TABLE project RENAME TO project_base');
    }
}
