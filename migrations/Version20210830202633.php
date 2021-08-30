<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210830202633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE supplies_brand (id INT AUTO_INCREMENT NOT NULL, household_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_633EF0C6E79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplies_category (id INT AUTO_INCREMENT NOT NULL, household_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_64071A7EE79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplies_item (id INT AUTO_INCREMENT NOT NULL, household_id INT NOT NULL, product_id INT NOT NULL, purchase_date DATE DEFAULT NULL, best_before_date DATE DEFAULT NULL, withdrawal_date DATE DEFAULT NULL, created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_191F0052E79FF843 (household_id), INDEX IDX_191F00524584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplies_measure (id INT AUTO_INCREMENT NOT NULL, household_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', physical_quantity VARCHAR(255) NOT NULL, INDEX IDX_16197979E79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplies_packaging (id INT AUTO_INCREMENT NOT NULL, household_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E437E89BE79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplies_product (id INT AUTO_INCREMENT NOT NULL, supply_id INT NOT NULL, brand_id INT NOT NULL, measure_id INT NOT NULL, packaging_id INT DEFAULT NULL, household_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, ean VARCHAR(13) DEFAULT NULL, quantity NUMERIC(10, 2) NOT NULL, organic_certification TINYINT(1) NOT NULL, minimum_number INT DEFAULT NULL, created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_455464F1FF28C0D8 (supply_id), INDEX IDX_455464F144F5D008 (brand_id), INDEX IDX_455464F15DA37D00 (measure_id), INDEX IDX_455464F14E7B3801 (packaging_id), INDEX IDX_455464F1E79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplies_supply (id INT AUTO_INCREMENT NOT NULL, household_id INT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', minimum_number INT DEFAULT NULL, INDEX IDX_C5D146C6E79FF843 (household_id), INDEX IDX_C5D146C612469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supplies_brand ADD CONSTRAINT FK_633EF0C6E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE supplies_category ADD CONSTRAINT FK_64071A7EE79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE supplies_item ADD CONSTRAINT FK_191F0052E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE supplies_item ADD CONSTRAINT FK_191F00524584665A FOREIGN KEY (product_id) REFERENCES supplies_product (id)');
        $this->addSql('ALTER TABLE supplies_measure ADD CONSTRAINT FK_16197979E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE supplies_packaging ADD CONSTRAINT FK_E437E89BE79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE supplies_product ADD CONSTRAINT FK_455464F1FF28C0D8 FOREIGN KEY (supply_id) REFERENCES supplies_supply (id)');
        $this->addSql('ALTER TABLE supplies_product ADD CONSTRAINT FK_455464F144F5D008 FOREIGN KEY (brand_id) REFERENCES supplies_brand (id)');
        $this->addSql('ALTER TABLE supplies_product ADD CONSTRAINT FK_455464F15DA37D00 FOREIGN KEY (measure_id) REFERENCES supplies_measure (id)');
        $this->addSql('ALTER TABLE supplies_product ADD CONSTRAINT FK_455464F14E7B3801 FOREIGN KEY (packaging_id) REFERENCES supplies_packaging (id)');
        $this->addSql('ALTER TABLE supplies_product ADD CONSTRAINT FK_455464F1E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE supplies_supply ADD CONSTRAINT FK_C5D146C6E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE supplies_supply ADD CONSTRAINT FK_C5D146C612469DE2 FOREIGN KEY (category_id) REFERENCES supplies_category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplies_product DROP FOREIGN KEY FK_455464F144F5D008');
        $this->addSql('ALTER TABLE supplies_supply DROP FOREIGN KEY FK_C5D146C612469DE2');
        $this->addSql('ALTER TABLE supplies_product DROP FOREIGN KEY FK_455464F15DA37D00');
        $this->addSql('ALTER TABLE supplies_product DROP FOREIGN KEY FK_455464F14E7B3801');
        $this->addSql('ALTER TABLE supplies_item DROP FOREIGN KEY FK_191F00524584665A');
        $this->addSql('ALTER TABLE supplies_product DROP FOREIGN KEY FK_455464F1FF28C0D8');
        $this->addSql('DROP TABLE supplies_brand');
        $this->addSql('DROP TABLE supplies_category');
        $this->addSql('DROP TABLE supplies_item');
        $this->addSql('DROP TABLE supplies_measure');
        $this->addSql('DROP TABLE supplies_packaging');
        $this->addSql('DROP TABLE supplies_product');
        $this->addSql('DROP TABLE supplies_supply');
    }
}
