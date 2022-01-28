<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220127121032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deposit_transaction ADD completed TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE transfer_transaction ADD completed TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE withdrawal_transaction ADD completed TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deposit_transaction DROP completed');
        $this->addSql('ALTER TABLE transfer_transaction DROP completed');
        $this->addSql('ALTER TABLE withdrawal_transaction DROP completed');
    }
}
