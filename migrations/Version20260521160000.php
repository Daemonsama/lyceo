<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les tables quiz_final_reussi et quiz_final_tentative';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE quiz_final_reussi (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, formation_id INT NOT NULL, score INT NOT NULL, date_reussite DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_QUIZ_FINAL_REUSSI_USER (user_id), INDEX IDX_QUIZ_FINAL_REUSSI_FORMATION (formation_id), UNIQUE INDEX uniq_user_formation_final (user_id, formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz_final_tentative (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, formation_id INT NOT NULL, score INT NOT NULL, date_tentative DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_QUIZ_FINAL_TENTATIVE_USER (user_id), INDEX IDX_QUIZ_FINAL_TENTATIVE_FORMATION (formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quiz_final_reussi ADD CONSTRAINT FK_QUIZ_FINAL_REUSSI_USER FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_final_reussi ADD CONSTRAINT FK_QUIZ_FINAL_REUSSI_FORMATION FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_final_tentative ADD CONSTRAINT FK_QUIZ_FINAL_TENTATIVE_USER FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_final_tentative ADD CONSTRAINT FK_QUIZ_FINAL_TENTATIVE_FORMATION FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quiz_final_reussi DROP FOREIGN KEY FK_QUIZ_FINAL_REUSSI_USER');
        $this->addSql('ALTER TABLE quiz_final_reussi DROP FOREIGN KEY FK_QUIZ_FINAL_REUSSI_FORMATION');
        $this->addSql('ALTER TABLE quiz_final_tentative DROP FOREIGN KEY FK_QUIZ_FINAL_TENTATIVE_USER');
        $this->addSql('ALTER TABLE quiz_final_tentative DROP FOREIGN KEY FK_QUIZ_FINAL_TENTATIVE_FORMATION');
        $this->addSql('DROP TABLE quiz_final_reussi');
        $this->addSql('DROP TABLE quiz_final_tentative');
    }
}
