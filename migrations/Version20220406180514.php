<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220406180514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplies_measure DROP FOREIGN KEY FK_16197979E79FF843');
        $this->addSql('DROP INDEX IDX_16197979E79FF843 ON supplies_measure');
        $this->addSql('ALTER TABLE supplies_measure ADD unit VARCHAR(255) NOT NULL, DROP household_id, DROP created_at');

        // Workaround needed in case that supplies_measure already contains rows (very rare, I hope!)
        $this->addSql('UPDATE supplies_measure SET unit = name');

        $this->addSql("INSERT INTO supplies_measure (id, name, physical_quantity, unit) VALUES (1, 'measure_milliliter_name', 'volume', 'measure_milliliter_unit')
            ON DUPLICATE KEY UPDATE name = 'measure_milliliter_name', physical_quantity = 'volume', unit = 'measure_milliliter_unit'");
        $this->addSql("INSERT INTO supplies_measure (id, name, physical_quantity, unit) VALUES (2, 'measure_liter_name', 'volume', 'measure_liter_unit')
            ON DUPLICATE KEY UPDATE name = 'measure_liter_name', physical_quantity = 'volume', unit = 'measure_liter_unit'");
        $this->addSql("INSERT INTO supplies_measure (id, name, physical_quantity, unit) VALUES (3, 'measure_gram_name', 'mass', 'measure_gram_unit')
            ON DUPLICATE KEY UPDATE name = 'measure_gram_name', physical_quantity = 'mass', unit = 'measure_gram_unit'");
        $this->addSql("INSERT INTO supplies_measure (id, name, physical_quantity, unit) VALUES (4, 'measure_kilogram_name', 'mass', 'measure_kilogram_unit')
            ON DUPLICATE KEY UPDATE name = 'measure_kilogram_name', physical_quantity = 'mass', unit = 'measure_kilogram_unit'");
        $this->addSql("INSERT INTO supplies_measure (id, name, physical_quantity, unit) VALUES (5, 'measure_piece_name', 'piece', 'measure_piece_unit')
            ON DUPLICATE KEY UPDATE name = 'measure_piece_name', physical_quantity = 'piece', unit = 'measure_piece_unit'");

        $this->addSql('CREATE UNIQUE INDEX UNIQ_161979795E237E06 ON supplies_measure (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16197979DCBB0C53 ON supplies_measure (unit)');
        $this->addSql('ALTER TABLE supplies_supply CHANGE category_id category_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_161979795E237E06 ON supplies_measure');
        $this->addSql('DROP INDEX UNIQ_16197979DCBB0C53 ON supplies_measure');
        $this->addSql('ALTER TABLE supplies_measure ADD household_id INT NOT NULL, ADD created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP unit');
        $this->addSql('ALTER TABLE supplies_measure ADD CONSTRAINT FK_16197979E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('CREATE INDEX IDX_16197979E79FF843 ON supplies_measure (household_id)');
        $this->addSql('ALTER TABLE supplies_supply CHANGE category_id category_id INT NOT NULL');
    }
}
