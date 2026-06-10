<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260608120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Codes promo par formation (admin + synchronisation Stripe)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE formation_promo_code (id INT AUTO_INCREMENT NOT NULL, formation_id INT NOT NULL, code VARCHAR(64) NOT NULL, discount_percent INT DEFAULT NULL, discount_amount INT DEFAULT NULL, active TINYINT(1) NOT NULL, stripe_promotion_code_id VARCHAR(255) DEFAULT NULL, stripe_coupon_id VARCHAR(255) DEFAULT NULL, INDEX IDX_FORMATION_PROMO_FORMATION (formation_id), UNIQUE INDEX uniq_formation_promo_code (formation_id, code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE formation_promo_code ADD CONSTRAINT FK_FORMATION_PROMO_FORMATION FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE formation_promo_code DROP FOREIGN KEY FK_FORMATION_PROMO_FORMATION');
        $this->addSql('DROP TABLE formation_promo_code');
    }
}
