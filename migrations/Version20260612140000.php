<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la durée de validité et la date de création aux codes promo';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE formation_promo_code ADD validity_days INT DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('UPDATE formation_promo_code SET created_at = NOW() WHERE created_at IS NULL');
        $this->addSql('ALTER TABLE formation_promo_code MODIFY created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE formation_promo_code DROP validity_days, DROP created_at');
    }
}
