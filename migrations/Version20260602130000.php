<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260602130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute l\'image d\'aperçu optionnelle sur les formations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE formation ADD apercu_filename VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE formation DROP apercu_filename');
    }
}
