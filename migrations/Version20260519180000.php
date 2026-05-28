<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260519180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rend le contenu texte des chapitres optionnel (vidéo seule autorisée)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chapitre CHANGE contenu contenu LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chapitre CHANGE contenu contenu LONGTEXT NOT NULL');
    }
}
