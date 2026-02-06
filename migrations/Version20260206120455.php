<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206120455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP project_type');
        $this->addSql('ALTER TABLE project DROP project_subtype');
        $this->addSql('ALTER TABLE project ALTER project_type_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project ADD project_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE project ADD project_subtype VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ALTER project_type_id DROP NOT NULL');
    }
}
