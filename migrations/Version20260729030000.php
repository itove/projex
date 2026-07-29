<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260729030000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add participating unit fields to project (supervision/construction/audit/building units and leaders)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD supervision_unit VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD supervision_unit_leader VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD construction_unit VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD construction_unit_leader VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD audit_unit VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD audit_unit_leader VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD building_unit VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD building_unit_leader VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project DROP supervision_unit');
        $this->addSql('ALTER TABLE project DROP supervision_unit_leader');
        $this->addSql('ALTER TABLE project DROP construction_unit');
        $this->addSql('ALTER TABLE project DROP construction_unit_leader');
        $this->addSql('ALTER TABLE project DROP audit_unit');
        $this->addSql('ALTER TABLE project DROP audit_unit_leader');
        $this->addSql('ALTER TABLE project DROP building_unit');
        $this->addSql('ALTER TABLE project DROP building_unit_leader');
    }
}
