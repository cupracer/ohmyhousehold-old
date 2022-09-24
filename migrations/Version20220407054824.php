<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220407054824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplies_packaging DROP FOREIGN KEY FK_E437E89BE79FF843');
        $this->addSql('DROP INDEX IDX_E437E89BE79FF843 ON supplies_packaging');
        $this->addSql('ALTER TABLE supplies_packaging DROP household_id, DROP created_at');

        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (1, 'packaging_tin_can')
            ON DUPLICATE KEY UPDATE name = 'packaging_tin_can'");
        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (2, 'packaging_canning_jar')
            ON DUPLICATE KEY UPDATE name = 'packaging_canning_jar'");
        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (3, 'packaging_glass')
            ON DUPLICATE KEY UPDATE name = 'packaging_glass'");
        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (4, 'packaging_plastic_bag')
            ON DUPLICATE KEY UPDATE name = 'packaging_plastic_bag'");
        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (5, 'packaging_glass_bottle')
            ON DUPLICATE KEY UPDATE name = 'packaging_glass_bottle'");
        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (6, 'packaging_plastic_bottle')
            ON DUPLICATE KEY UPDATE name = 'packaging_plastic_bottle'");
        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (7, 'packaging_tube')
            ON DUPLICATE KEY UPDATE name = 'packaging_tube'");
        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (8, 'packaging_aluminum')
            ON DUPLICATE KEY UPDATE name = 'packaging_aluminum'");
        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (9, 'packaging_net')
            ON DUPLICATE KEY UPDATE name = 'packaging_net'");
        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (10, 'packaging_cardboard')
            ON DUPLICATE KEY UPDATE name = 'packaging_cardboard'");
        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (11, 'packaging_paper')
            ON DUPLICATE KEY UPDATE name = 'packaging_paper'");
        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (12, 'packaging_tetra_pak')
            ON DUPLICATE KEY UPDATE name = 'packaging_tetra_pak'");
        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (13, 'packaging_plastic_can')
            ON DUPLICATE KEY UPDATE name = 'packaging_plastic_can'");

        $this->addSql('CREATE UNIQUE INDEX UNIQ_E437E89B5E237E06 ON supplies_packaging (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_E437E89B5E237E06 ON supplies_packaging');
        $this->addSql('ALTER TABLE supplies_packaging ADD household_id INT NOT NULL, ADD created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE supplies_packaging ADD CONSTRAINT FK_E437E89BE79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('CREATE INDEX IDX_E437E89BE79FF843 ON supplies_packaging (household_id)');
    }
}
