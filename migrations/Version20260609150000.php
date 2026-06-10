<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260609150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Quatrième badge optionnel sur la page d\'accueil';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE home_page_content ADD about_badge4 VARCHAR(128) NOT NULL DEFAULT ''");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE home_page_content DROP about_badge4');
    }
}
