<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260609140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Contenu complet personnalisable de la page d\'accueil';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE home_page_content
            ADD hero_logo_filename VARCHAR(255) DEFAULT NULL,
            ADD hero_cta_primary_label VARCHAR(128) NOT NULL DEFAULT 'Découvrir les modules',
            ADD hero_cta_primary_url VARCHAR(255) NOT NULL DEFAULT '/formation',
            ADD hero_cta_secondary_label VARCHAR(128) NOT NULL DEFAULT 'Nous contacter',
            ADD hero_cta_secondary_url VARCHAR(255) NOT NULL DEFAULT '#contact',
            ADD stat1_value VARCHAR(64) NOT NULL DEFAULT '25+',
            ADD stat1_icon VARCHAR(64) DEFAULT NULL,
            ADD stat1_label VARCHAR(255) NOT NULL DEFAULT 'Années d''expérience',
            ADD stat2_value VARCHAR(64) NOT NULL DEFAULT '100%',
            ADD stat2_icon VARCHAR(64) DEFAULT NULL,
            ADD stat2_label VARCHAR(255) NOT NULL DEFAULT 'Modules interactifs',
            ADD stat3_value VARCHAR(64) DEFAULT NULL,
            ADD stat3_icon VARCHAR(64) DEFAULT 'star-fill',
            ADD stat3_label VARCHAR(255) NOT NULL DEFAULT 'Coach certifié',
            ADD stat4_value VARCHAR(64) DEFAULT NULL,
            ADD stat4_icon VARCHAR(64) DEFAULT 'geo-alt-fill',
            ADD stat4_label VARCHAR(255) NOT NULL DEFAULT 'Aurillac (15)',
            ADD mission_title VARCHAR(255) NOT NULL DEFAULT 'Notre mission',
            ADD mission_lead TEXT NOT NULL DEFAULT (''),
            ADD mission_body TEXT NOT NULL DEFAULT (''),
            ADD mission_card_title VARCHAR(255) NOT NULL DEFAULT 'Nos objectifs',
            ADD mission_list_items TEXT NOT NULL DEFAULT (''),
            ADD audience_title VARCHAR(255) NOT NULL DEFAULT 'Pour qui ?',
            ADD audience_lead TEXT NOT NULL DEFAULT (''),
            ADD audience_card1_title VARCHAR(255) NOT NULL DEFAULT 'Module INTRA',
            ADD audience_card1_text TEXT NOT NULL DEFAULT (''),
            ADD audience_card1_icon VARCHAR(64) NOT NULL DEFAULT 'building',
            ADD audience_card2_title VARCHAR(255) NOT NULL DEFAULT 'Module INTER',
            ADD audience_card2_text TEXT NOT NULL DEFAULT (''),
            ADD audience_card2_icon VARCHAR(64) NOT NULL DEFAULT 'people',
            ADD services_title VARCHAR(255) NOT NULL DEFAULT 'Nos services',
            ADD services_lead TEXT NOT NULL DEFAULT (''),
            ADD service1_title VARCHAR(255) NOT NULL DEFAULT 'Coaching Individualisé',
            ADD service1_text TEXT NOT NULL DEFAULT (''),
            ADD service1_icon VARCHAR(64) NOT NULL DEFAULT 'person-check',
            ADD service2_title VARCHAR(255) NOT NULL DEFAULT 'Modules Pratiques',
            ADD service2_text TEXT NOT NULL DEFAULT (''),
            ADD service2_icon VARCHAR(64) NOT NULL DEFAULT 'book',
            ADD service3_title VARCHAR(255) NOT NULL DEFAULT 'Objectifs Mesurables',
            ADD service3_text TEXT NOT NULL DEFAULT (''),
            ADD service3_icon VARCHAR(64) NOT NULL DEFAULT 'graph-up-arrow',
            ADD contact_title VARCHAR(255) NOT NULL DEFAULT 'Nous contacter',
            ADD contact_lead TEXT NOT NULL DEFAULT ('')");

        $this->addSql("UPDATE home_page_content SET
            mission_lead = 'Vous accompagner dans le développement de votre <strong>AGILITÉ</strong>, votre <strong>CARE</strong>, votre <strong>FLOW</strong> et votre <strong>ENGAGEMENT</strong>.',
            mission_body = 'Nous vous délivrons les outils nécessaires pour repenser et déployer votre mission en tant que <strong>coach manager</strong> ou membre d''équipe. Apprenez à communiquer efficacement, maîtrisez la gestion hybride et créez de la valeur partagée.',
            mission_list_items = 'Développer votre efficacité professionnelle\nRévéler votre talent unique et votre potentiel\nAccompagner votre équipe vers l''excellence\nCréer de la valeur partagée dans votre entreprise',
            audience_lead = 'Nos modules s''adressent aux équipes et managers qui veulent progresser',
            audience_card1_text = 'Pour votre équipe ou vos managers de différents services au sein de votre entreprise.',
            audience_card2_text = 'Pour managers et équipes de différentes entreprises qui veulent partager leurs expériences.',
            services_lead = 'Un accompagnement complet pour votre développement',
            service1_text = 'Des séances personnalisées pour identifier et surmonter vos obstacles spécifiques.',
            service2_text = 'Des programmes concrets et interactifs pour acquérir des compétences rapidement.',
            service3_text = 'Établissement d''objectifs clairs pour suivre vos progrès et célébrer vos réussites.',
            contact_lead = 'Une question sur nos modules ? Écrivez-nous, nous vous répondrons dans les meilleurs délais.'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE home_page_content
            DROP hero_logo_filename,
            DROP hero_cta_primary_label,
            DROP hero_cta_primary_url,
            DROP hero_cta_secondary_label,
            DROP hero_cta_secondary_url,
            DROP stat1_value,
            DROP stat1_icon,
            DROP stat1_label,
            DROP stat2_value,
            DROP stat2_icon,
            DROP stat2_label,
            DROP stat3_value,
            DROP stat3_icon,
            DROP stat3_label,
            DROP stat4_value,
            DROP stat4_icon,
            DROP stat4_label,
            DROP mission_title,
            DROP mission_lead,
            DROP mission_body,
            DROP mission_card_title,
            DROP mission_list_items,
            DROP audience_title,
            DROP audience_lead,
            DROP audience_card1_title,
            DROP audience_card1_text,
            DROP audience_card1_icon,
            DROP audience_card2_title,
            DROP audience_card2_text,
            DROP audience_card2_icon,
            DROP services_title,
            DROP services_lead,
            DROP service1_title,
            DROP service1_text,
            DROP service1_icon,
            DROP service2_title,
            DROP service2_text,
            DROP service2_icon,
            DROP service3_title,
            DROP service3_text,
            DROP service3_icon,
            DROP contact_title,
            DROP contact_lead');
    }
}
