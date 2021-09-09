<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210909185245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE periodic_transfer_transaction DROP FOREIGN KEY FK_FD94E2DE35DD462B');
        $this->addSql('DROP INDEX IDX_FD94E2DE35DD462B ON periodic_transfer_transaction');
        $this->addSql('ALTER TABLE periodic_transfer_transaction DROP booking_category_id');
        $this->addSql('ALTER TABLE transfer_transaction DROP FOREIGN KEY FK_AB3B923335DD462B');
        $this->addSql('DROP INDEX IDX_AB3B923335DD462B ON transfer_transaction');
        $this->addSql('ALTER TABLE transfer_transaction DROP booking_category_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE periodic_transfer_transaction ADD booking_category_id INT NOT NULL');
        $this->addSql('ALTER TABLE periodic_transfer_transaction ADD CONSTRAINT FK_FD94E2DE35DD462B FOREIGN KEY (booking_category_id) REFERENCES booking_category (id)');
        $this->addSql('CREATE INDEX IDX_FD94E2DE35DD462B ON periodic_transfer_transaction (booking_category_id)');
        $this->addSql('ALTER TABLE transfer_transaction ADD booking_category_id INT NOT NULL');
        $this->addSql('ALTER TABLE transfer_transaction ADD CONSTRAINT FK_AB3B923335DD462B FOREIGN KEY (booking_category_id) REFERENCES booking_category (id)');
        $this->addSql('CREATE INDEX IDX_AB3B923335DD462B ON transfer_transaction (booking_category_id)');
    }
}
