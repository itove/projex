<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260214084338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project ADD registered_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD registrant_organization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project DROP registrant_organization');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE27E92E18 FOREIGN KEY (registered_by_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE60C3DA2C FOREIGN KEY (registrant_organization_id) REFERENCES org (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE60C3DA2C ON project (registrant_organization_id)');
        $this->addSql('CREATE INDEX idx_project_registered_by ON project (registered_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE27E92E18');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE60C3DA2C');
        $this->addSql('DROP INDEX IDX_2FB3D0EE60C3DA2C');
        $this->addSql('DROP INDEX idx_project_registered_by');
        $this->addSql('ALTER TABLE project ADD registrant_organization VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE project DROP registered_by_id');
        $this->addSql('ALTER TABLE project DROP registrant_organization_id');
    }
}
