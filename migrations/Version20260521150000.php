<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la table quiz_tentative pour compter les échecs au quiz';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE quiz_tentative (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, quiz_id INT NOT NULL, score INT NOT NULL, date_tentative DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_QUIZ_TENTATIVE_USER (user_id), INDEX IDX_QUIZ_TENTATIVE_QUIZ (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quiz_tentative ADD CONSTRAINT FK_QUIZ_TENTATIVE_USER FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_tentative ADD CONSTRAINT FK_QUIZ_TENTATIVE_QUIZ FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quiz_tentative DROP FOREIGN KEY FK_QUIZ_TENTATIVE_USER');
        $this->addSql('ALTER TABLE quiz_tentative DROP FOREIGN KEY FK_QUIZ_TENTATIVE_QUIZ');
        $this->addSql('DROP TABLE quiz_tentative');
    }
}
