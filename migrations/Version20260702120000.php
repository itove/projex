<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260702120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optional lifecycle_stage to project_task for stage-scoped tasks';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project_task ADD lifecycle_stage VARCHAR(32) DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_project_task_lifecycle_stage ON project_task (lifecycle_stage)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_project_task_lifecycle_stage');
        $this->addSql('ALTER TABLE project_task DROP lifecycle_stage');
    }
}
