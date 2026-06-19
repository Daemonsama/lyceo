<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260616120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renomme les libellés UI « formations » en « modules » dans le contenu de la page d\'accueil';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE home_page_content SET
            hero_lead = REPLACE(hero_lead, 'formations professionnelles', 'modules professionnels'),
            hero_cta_primary_label = REPLACE(hero_cta_primary_label, 'formations', 'modules'),
            stat2_label = REPLACE(stat2_label, 'Formations', 'Modules'),
            audience_lead = REPLACE(audience_lead, 'formations', 'modules'),
            audience_card1_title = REPLACE(audience_card1_title, 'Formation INTRA', 'Module INTRA'),
            audience_card2_title = REPLACE(audience_card2_title, 'Formation INTER', 'Module INTER'),
            service2_title = REPLACE(service2_title, 'Formations', 'Modules'),
            contact_lead = REPLACE(contact_lead, 'formations', 'modules')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE home_page_content SET
            hero_lead = REPLACE(hero_lead, 'modules professionnels', 'formations professionnelles'),
            hero_cta_primary_label = REPLACE(hero_cta_primary_label, 'modules', 'formations'),
            stat2_label = REPLACE(stat2_label, 'Modules', 'Formations'),
            audience_lead = REPLACE(audience_lead, 'modules', 'formations'),
            audience_card1_title = REPLACE(audience_card1_title, 'Module INTRA', 'Formation INTRA'),
            audience_card2_title = REPLACE(audience_card2_title, 'Module INTER', 'Formation INTER'),
            service2_title = REPLACE(service2_title, 'Modules', 'Formations'),
            contact_lead = REPLACE(contact_lead, 'modules', 'formations')");
    }
}
