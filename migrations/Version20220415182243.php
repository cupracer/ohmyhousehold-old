<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220415182243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO supplies_packaging (id, name) VALUES (14, 'packaging_foil')
            ON DUPLICATE KEY UPDATE name = 'packaging_foil'");

        $this->addSql("INSERT INTO supplies_measure (id, name, physical_quantity, unit) VALUES (6, 'measure_meter_name', 'length', 'measure_meter_unit')
            ON DUPLICATE KEY UPDATE name = 'measure_meter_name', physical_quantity = 'length', unit = 'measure_meter_unit'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM supplies_measure WHERE id = 6');

        $this->addSql('DELETE FROM supplies_packaging WHERE id = 14');
    }
}
