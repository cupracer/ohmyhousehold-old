<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221102174907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE supplies_storage_location (id INT AUTO_INCREMENT NOT NULL, household_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4506B598E79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supplies_storage_location ADD CONSTRAINT FK_4506B598E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE supplies_item ADD storage_location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supplies_item ADD CONSTRAINT FK_191F0052CDDD8AF FOREIGN KEY (storage_location_id) REFERENCES supplies_storage_location (id)');
        $this->addSql('CREATE INDEX IDX_191F0052CDDD8AF ON supplies_item (storage_location_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplies_item DROP FOREIGN KEY FK_191F0052CDDD8AF');
        $this->addSql('DROP TABLE supplies_storage_location');
        $this->addSql('DROP INDEX IDX_191F0052CDDD8AF ON supplies_item');
        $this->addSql('ALTER TABLE supplies_item DROP storage_location_id');
    }
}
