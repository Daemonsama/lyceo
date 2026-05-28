<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260518120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les tables quiz, question_quiz, reponse_quiz et quiz_reussi';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, chapitre_id INT NOT NULL, titre VARCHAR(255) NOT NULL, seuil_reussite INT DEFAULT 70 NOT NULL, UNIQUE INDEX uniq_quiz_chapitre (chapitre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question_quiz (id INT AUTO_INCREMENT NOT NULL, quiz_id INT NOT NULL, enonce LONGTEXT NOT NULL, ordre INT NOT NULL, INDEX IDX_8C5B2E1E853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse_quiz (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, libelle VARCHAR(500) NOT NULL, est_correcte TINYINT(1) NOT NULL, INDEX IDX_2F7A7C1E1E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz_reussi (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, quiz_id INT NOT NULL, score INT NOT NULL, date_reussite DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4A8B9C2EA76ED395 (user_id), INDEX IDX_4A8B9C2E853CD175 (quiz_id), UNIQUE INDEX uniq_user_quiz (user_id, quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92A9F5E3B1 FOREIGN KEY (chapitre_id) REFERENCES chapitre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question_quiz ADD CONSTRAINT FK_8C5B2E1E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_quiz ADD CONSTRAINT FK_2F7A7C1E1E27F6BF FOREIGN KEY (question_id) REFERENCES question_quiz (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_reussi ADD CONSTRAINT FK_4A8B9C2EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_reussi ADD CONSTRAINT FK_4A8B9C2E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quiz_reussi DROP FOREIGN KEY FK_4A8B9C2EA76ED395');
        $this->addSql('ALTER TABLE quiz_reussi DROP FOREIGN KEY FK_4A8B9C2E853CD175');
        $this->addSql('ALTER TABLE reponse_quiz DROP FOREIGN KEY FK_2F7A7C1E1E27F6BF');
        $this->addSql('ALTER TABLE question_quiz DROP FOREIGN KEY FK_8C5B2E1E853CD175');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92A9F5E3B1');
        $this->addSql('DROP TABLE quiz_reussi');
        $this->addSql('DROP TABLE reponse_quiz');
        $this->addSql('DROP TABLE question_quiz');
        $this->addSql('DROP TABLE quiz');
    }
}
