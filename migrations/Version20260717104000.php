<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260717104000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace project.progress_report_interval_days with progress_report_interval (week/month)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD progress_report_interval VARCHAR(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE project DROP progress_report_interval_days');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD progress_report_interval_days INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project DROP progress_report_interval');
    }
}
