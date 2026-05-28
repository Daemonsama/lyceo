<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260519140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute media_url sur chapitre pour les vidéos Google Drive / liens externes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chapitre ADD media_url VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chapitre DROP media_url');
    }
}
