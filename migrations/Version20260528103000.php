<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute un champ media nullable pour la présentation de formation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE formation ADD media VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE formation DROP media');
    }
}

