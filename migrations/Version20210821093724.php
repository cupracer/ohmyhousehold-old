<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210821093724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account_holder (id INT AUTO_INCREMENT NOT NULL, household_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME(6) NOT NULL, INDEX IDX_F19CA6CEE79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE api_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) NOT NULL, created_at DATETIME(6) NOT NULL, expires_at DATETIME(6) DEFAULT NULL, description VARCHAR(255) NOT NULL, last_used_at DATETIME(6) DEFAULT NULL, hash_algorithm VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_7BA2F5EB5F37A13B (token), INDEX IDX_7BA2F5EBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset_account (id INT AUTO_INCREMENT NOT NULL, household_id INT NOT NULL, initial_balance NUMERIC(10, 2) NOT NULL, iban VARCHAR(34) DEFAULT NULL, created_at DATETIME(6) NOT NULL, name VARCHAR(255) NOT NULL, account_type VARCHAR(10) NOT NULL, INDEX IDX_1ED817F2E79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset_account_household_user (asset_account_id INT NOT NULL, household_user_id INT NOT NULL, INDEX IDX_A55DEEE45D8ABCE0 (asset_account_id), INDEX IDX_A55DEEE4CA9A39BB (household_user_id), PRIMARY KEY(asset_account_id, household_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking_category (id INT AUTO_INCREMENT NOT NULL, household_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME(6) NOT NULL, INDEX IDX_3D78874CE79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deposit_transaction (id INT AUTO_INCREMENT NOT NULL, source_id INT DEFAULT NULL, destination_id INT DEFAULT NULL, household_user_id INT NOT NULL, booking_category_id INT NOT NULL, periodic_deposit_transaction_id INT DEFAULT NULL, household_id INT NOT NULL, booking_date DATE NOT NULL, private TINYINT(1) NOT NULL, booking_period_offset INT NOT NULL, created_at DATETIME(6) NOT NULL, amount NUMERIC(10, 2) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_907A7426953C1C61 (source_id), INDEX IDX_907A7426816C6140 (destination_id), INDEX IDX_907A7426CA9A39BB (household_user_id), INDEX IDX_907A742635DD462B (booking_category_id), INDEX IDX_907A74261AD77110 (periodic_deposit_transaction_id), INDEX IDX_907A7426E79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expense_account (id INT AUTO_INCREMENT NOT NULL, household_id INT NOT NULL, account_holder_id INT DEFAULT NULL, initial_balance NUMERIC(10, 2) NOT NULL, iban VARCHAR(34) DEFAULT NULL, created_at DATETIME(6) NOT NULL, INDEX IDX_102D1D9EE79FF843 (household_id), INDEX IDX_102D1D9EFC94BA8B (account_holder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE household (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME(6) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE household_user (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, household_id INT NOT NULL, is_admin TINYINT(1) NOT NULL, created_at DATETIME(6) NOT NULL, INDEX IDX_8CCC41A8A76ED395 (user_id), INDEX IDX_8CCC41A8E79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE periodic_deposit_transaction (id INT AUTO_INCREMENT NOT NULL, source_id INT DEFAULT NULL, destination_id INT DEFAULT NULL, household_user_id INT NOT NULL, booking_category_id INT NOT NULL, household_id INT NOT NULL, created_at DATETIME(6) NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, booking_interval INT NOT NULL, booking_day_of_month INT NOT NULL, private TINYINT(1) NOT NULL, booking_period_offset INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_8CCEBA13953C1C61 (source_id), INDEX IDX_8CCEBA13816C6140 (destination_id), INDEX IDX_8CCEBA13CA9A39BB (household_user_id), INDEX IDX_8CCEBA1335DD462B (booking_category_id), INDEX IDX_8CCEBA13E79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE periodic_transfer_transaction (id INT AUTO_INCREMENT NOT NULL, source_id INT DEFAULT NULL, destination_id INT DEFAULT NULL, household_user_id INT NOT NULL, booking_category_id INT NOT NULL, household_id INT NOT NULL, created_at DATETIME(6) NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, booking_interval INT NOT NULL, booking_day_of_month INT NOT NULL, private TINYINT(1) NOT NULL, booking_period_offset INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_FD94E2DE953C1C61 (source_id), INDEX IDX_FD94E2DE816C6140 (destination_id), INDEX IDX_FD94E2DECA9A39BB (household_user_id), INDEX IDX_FD94E2DE35DD462B (booking_category_id), INDEX IDX_FD94E2DEE79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE periodic_withdrawal_transaction (id INT AUTO_INCREMENT NOT NULL, source_id INT DEFAULT NULL, destination_id INT DEFAULT NULL, household_user_id INT NOT NULL, booking_category_id INT NOT NULL, household_id INT NOT NULL, created_at DATETIME(6) NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, booking_interval INT NOT NULL, booking_day_of_month INT NOT NULL, private TINYINT(1) NOT NULL, booking_period_offset INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_235BFF36953C1C61 (source_id), INDEX IDX_235BFF36816C6140 (destination_id), INDEX IDX_235BFF36CA9A39BB (household_user_id), INDEX IDX_235BFF3635DD462B (booking_category_id), INDEX IDX_235BFF36E79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE revenue_account (id INT AUTO_INCREMENT NOT NULL, household_id INT NOT NULL, account_holder_id INT DEFAULT NULL, initial_balance NUMERIC(10, 2) NOT NULL, iban VARCHAR(34) DEFAULT NULL, created_at DATETIME(6) NOT NULL, INDEX IDX_B3FD6E66E79FF843 (household_id), INDEX IDX_B3FD6E66FC94BA8B (account_holder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfer_transaction (id INT AUTO_INCREMENT NOT NULL, source_id INT DEFAULT NULL, destination_id INT DEFAULT NULL, household_user_id INT NOT NULL, booking_category_id INT NOT NULL, periodic_transfer_transaction_id INT DEFAULT NULL, household_id INT NOT NULL, booking_date DATE NOT NULL, private TINYINT(1) NOT NULL, booking_period_offset INT NOT NULL, created_at DATETIME(6) NOT NULL, amount NUMERIC(10, 2) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_AB3B9233953C1C61 (source_id), INDEX IDX_AB3B9233816C6140 (destination_id), INDEX IDX_AB3B9233CA9A39BB (household_user_id), INDEX IDX_AB3B923335DD462B (booking_category_id), INDEX IDX_AB3B92337A7C1BC4 (periodic_transfer_transaction_id), INDEX IDX_AB3B9233E79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, created_at DATETIME(6) NOT NULL, updated_at DATETIME(6) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_profile (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, forenames VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, created_at DATETIME(6) NOT NULL, updated_at DATETIME(6) NOT NULL, locale VARCHAR(10) NOT NULL, UNIQUE INDEX UNIQ_D95AB405A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE withdrawal_transaction (id INT AUTO_INCREMENT NOT NULL, source_id INT NOT NULL, destination_id INT NOT NULL, household_user_id INT NOT NULL, booking_category_id INT NOT NULL, periodic_withdrawal_transaction_id INT DEFAULT NULL, household_id INT NOT NULL, booking_date DATE NOT NULL, private TINYINT(1) NOT NULL, booking_period_offset INT NOT NULL, created_at DATETIME(6) NOT NULL, amount NUMERIC(10, 2) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_988E5504953C1C61 (source_id), INDEX IDX_988E5504816C6140 (destination_id), INDEX IDX_988E5504CA9A39BB (household_user_id), INDEX IDX_988E550435DD462B (booking_category_id), INDEX IDX_988E5504293A293F (periodic_withdrawal_transaction_id), INDEX IDX_988E5504E79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE account_holder ADD CONSTRAINT FK_F19CA6CEE79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset_account ADD CONSTRAINT FK_1ED817F2E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE asset_account_household_user ADD CONSTRAINT FK_A55DEEE45D8ABCE0 FOREIGN KEY (asset_account_id) REFERENCES asset_account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE asset_account_household_user ADD CONSTRAINT FK_A55DEEE4CA9A39BB FOREIGN KEY (household_user_id) REFERENCES household_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking_category ADD CONSTRAINT FK_3D78874CE79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE deposit_transaction ADD CONSTRAINT FK_907A7426953C1C61 FOREIGN KEY (source_id) REFERENCES revenue_account (id)');
        $this->addSql('ALTER TABLE deposit_transaction ADD CONSTRAINT FK_907A7426816C6140 FOREIGN KEY (destination_id) REFERENCES asset_account (id)');
        $this->addSql('ALTER TABLE deposit_transaction ADD CONSTRAINT FK_907A7426CA9A39BB FOREIGN KEY (household_user_id) REFERENCES household_user (id)');
        $this->addSql('ALTER TABLE deposit_transaction ADD CONSTRAINT FK_907A742635DD462B FOREIGN KEY (booking_category_id) REFERENCES booking_category (id)');
        $this->addSql('ALTER TABLE deposit_transaction ADD CONSTRAINT FK_907A74261AD77110 FOREIGN KEY (periodic_deposit_transaction_id) REFERENCES periodic_deposit_transaction (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE deposit_transaction ADD CONSTRAINT FK_907A7426E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE expense_account ADD CONSTRAINT FK_102D1D9EE79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE expense_account ADD CONSTRAINT FK_102D1D9EFC94BA8B FOREIGN KEY (account_holder_id) REFERENCES account_holder (id)');
        $this->addSql('ALTER TABLE household_user ADD CONSTRAINT FK_8CCC41A8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE household_user ADD CONSTRAINT FK_8CCC41A8E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE periodic_deposit_transaction ADD CONSTRAINT FK_8CCEBA13953C1C61 FOREIGN KEY (source_id) REFERENCES revenue_account (id)');
        $this->addSql('ALTER TABLE periodic_deposit_transaction ADD CONSTRAINT FK_8CCEBA13816C6140 FOREIGN KEY (destination_id) REFERENCES asset_account (id)');
        $this->addSql('ALTER TABLE periodic_deposit_transaction ADD CONSTRAINT FK_8CCEBA13CA9A39BB FOREIGN KEY (household_user_id) REFERENCES household_user (id)');
        $this->addSql('ALTER TABLE periodic_deposit_transaction ADD CONSTRAINT FK_8CCEBA1335DD462B FOREIGN KEY (booking_category_id) REFERENCES booking_category (id)');
        $this->addSql('ALTER TABLE periodic_deposit_transaction ADD CONSTRAINT FK_8CCEBA13E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE periodic_transfer_transaction ADD CONSTRAINT FK_FD94E2DE953C1C61 FOREIGN KEY (source_id) REFERENCES asset_account (id)');
        $this->addSql('ALTER TABLE periodic_transfer_transaction ADD CONSTRAINT FK_FD94E2DE816C6140 FOREIGN KEY (destination_id) REFERENCES asset_account (id)');
        $this->addSql('ALTER TABLE periodic_transfer_transaction ADD CONSTRAINT FK_FD94E2DECA9A39BB FOREIGN KEY (household_user_id) REFERENCES household_user (id)');
        $this->addSql('ALTER TABLE periodic_transfer_transaction ADD CONSTRAINT FK_FD94E2DE35DD462B FOREIGN KEY (booking_category_id) REFERENCES booking_category (id)');
        $this->addSql('ALTER TABLE periodic_transfer_transaction ADD CONSTRAINT FK_FD94E2DEE79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE periodic_withdrawal_transaction ADD CONSTRAINT FK_235BFF36953C1C61 FOREIGN KEY (source_id) REFERENCES asset_account (id)');
        $this->addSql('ALTER TABLE periodic_withdrawal_transaction ADD CONSTRAINT FK_235BFF36816C6140 FOREIGN KEY (destination_id) REFERENCES expense_account (id)');
        $this->addSql('ALTER TABLE periodic_withdrawal_transaction ADD CONSTRAINT FK_235BFF36CA9A39BB FOREIGN KEY (household_user_id) REFERENCES household_user (id)');
        $this->addSql('ALTER TABLE periodic_withdrawal_transaction ADD CONSTRAINT FK_235BFF3635DD462B FOREIGN KEY (booking_category_id) REFERENCES booking_category (id)');
        $this->addSql('ALTER TABLE periodic_withdrawal_transaction ADD CONSTRAINT FK_235BFF36E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE revenue_account ADD CONSTRAINT FK_B3FD6E66E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE revenue_account ADD CONSTRAINT FK_B3FD6E66FC94BA8B FOREIGN KEY (account_holder_id) REFERENCES account_holder (id)');
        $this->addSql('ALTER TABLE transfer_transaction ADD CONSTRAINT FK_AB3B9233953C1C61 FOREIGN KEY (source_id) REFERENCES asset_account (id)');
        $this->addSql('ALTER TABLE transfer_transaction ADD CONSTRAINT FK_AB3B9233816C6140 FOREIGN KEY (destination_id) REFERENCES asset_account (id)');
        $this->addSql('ALTER TABLE transfer_transaction ADD CONSTRAINT FK_AB3B9233CA9A39BB FOREIGN KEY (household_user_id) REFERENCES household_user (id)');
        $this->addSql('ALTER TABLE transfer_transaction ADD CONSTRAINT FK_AB3B923335DD462B FOREIGN KEY (booking_category_id) REFERENCES booking_category (id)');
        $this->addSql('ALTER TABLE transfer_transaction ADD CONSTRAINT FK_AB3B92337A7C1BC4 FOREIGN KEY (periodic_transfer_transaction_id) REFERENCES periodic_transfer_transaction (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transfer_transaction ADD CONSTRAINT FK_AB3B9233E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE withdrawal_transaction ADD CONSTRAINT FK_988E5504953C1C61 FOREIGN KEY (source_id) REFERENCES asset_account (id)');
        $this->addSql('ALTER TABLE withdrawal_transaction ADD CONSTRAINT FK_988E5504816C6140 FOREIGN KEY (destination_id) REFERENCES expense_account (id)');
        $this->addSql('ALTER TABLE withdrawal_transaction ADD CONSTRAINT FK_988E5504CA9A39BB FOREIGN KEY (household_user_id) REFERENCES household_user (id)');
        $this->addSql('ALTER TABLE withdrawal_transaction ADD CONSTRAINT FK_988E550435DD462B FOREIGN KEY (booking_category_id) REFERENCES booking_category (id)');
        $this->addSql('ALTER TABLE withdrawal_transaction ADD CONSTRAINT FK_988E5504293A293F FOREIGN KEY (periodic_withdrawal_transaction_id) REFERENCES periodic_withdrawal_transaction (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE withdrawal_transaction ADD CONSTRAINT FK_988E5504E79FF843 FOREIGN KEY (household_id) REFERENCES household (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expense_account DROP FOREIGN KEY FK_102D1D9EFC94BA8B');
        $this->addSql('ALTER TABLE revenue_account DROP FOREIGN KEY FK_B3FD6E66FC94BA8B');
        $this->addSql('ALTER TABLE asset_account_household_user DROP FOREIGN KEY FK_A55DEEE45D8ABCE0');
        $this->addSql('ALTER TABLE deposit_transaction DROP FOREIGN KEY FK_907A7426816C6140');
        $this->addSql('ALTER TABLE periodic_deposit_transaction DROP FOREIGN KEY FK_8CCEBA13816C6140');
        $this->addSql('ALTER TABLE periodic_transfer_transaction DROP FOREIGN KEY FK_FD94E2DE953C1C61');
        $this->addSql('ALTER TABLE periodic_transfer_transaction DROP FOREIGN KEY FK_FD94E2DE816C6140');
        $this->addSql('ALTER TABLE periodic_withdrawal_transaction DROP FOREIGN KEY FK_235BFF36953C1C61');
        $this->addSql('ALTER TABLE transfer_transaction DROP FOREIGN KEY FK_AB3B9233953C1C61');
        $this->addSql('ALTER TABLE transfer_transaction DROP FOREIGN KEY FK_AB3B9233816C6140');
        $this->addSql('ALTER TABLE withdrawal_transaction DROP FOREIGN KEY FK_988E5504953C1C61');
        $this->addSql('ALTER TABLE deposit_transaction DROP FOREIGN KEY FK_907A742635DD462B');
        $this->addSql('ALTER TABLE periodic_deposit_transaction DROP FOREIGN KEY FK_8CCEBA1335DD462B');
        $this->addSql('ALTER TABLE periodic_transfer_transaction DROP FOREIGN KEY FK_FD94E2DE35DD462B');
        $this->addSql('ALTER TABLE periodic_withdrawal_transaction DROP FOREIGN KEY FK_235BFF3635DD462B');
        $this->addSql('ALTER TABLE transfer_transaction DROP FOREIGN KEY FK_AB3B923335DD462B');
        $this->addSql('ALTER TABLE withdrawal_transaction DROP FOREIGN KEY FK_988E550435DD462B');
        $this->addSql('ALTER TABLE periodic_withdrawal_transaction DROP FOREIGN KEY FK_235BFF36816C6140');
        $this->addSql('ALTER TABLE withdrawal_transaction DROP FOREIGN KEY FK_988E5504816C6140');
        $this->addSql('ALTER TABLE account_holder DROP FOREIGN KEY FK_F19CA6CEE79FF843');
        $this->addSql('ALTER TABLE asset_account DROP FOREIGN KEY FK_1ED817F2E79FF843');
        $this->addSql('ALTER TABLE booking_category DROP FOREIGN KEY FK_3D78874CE79FF843');
        $this->addSql('ALTER TABLE deposit_transaction DROP FOREIGN KEY FK_907A7426E79FF843');
        $this->addSql('ALTER TABLE expense_account DROP FOREIGN KEY FK_102D1D9EE79FF843');
        $this->addSql('ALTER TABLE household_user DROP FOREIGN KEY FK_8CCC41A8E79FF843');
        $this->addSql('ALTER TABLE periodic_deposit_transaction DROP FOREIGN KEY FK_8CCEBA13E79FF843');
        $this->addSql('ALTER TABLE periodic_transfer_transaction DROP FOREIGN KEY FK_FD94E2DEE79FF843');
        $this->addSql('ALTER TABLE periodic_withdrawal_transaction DROP FOREIGN KEY FK_235BFF36E79FF843');
        $this->addSql('ALTER TABLE revenue_account DROP FOREIGN KEY FK_B3FD6E66E79FF843');
        $this->addSql('ALTER TABLE transfer_transaction DROP FOREIGN KEY FK_AB3B9233E79FF843');
        $this->addSql('ALTER TABLE withdrawal_transaction DROP FOREIGN KEY FK_988E5504E79FF843');
        $this->addSql('ALTER TABLE asset_account_household_user DROP FOREIGN KEY FK_A55DEEE4CA9A39BB');
        $this->addSql('ALTER TABLE deposit_transaction DROP FOREIGN KEY FK_907A7426CA9A39BB');
        $this->addSql('ALTER TABLE periodic_deposit_transaction DROP FOREIGN KEY FK_8CCEBA13CA9A39BB');
        $this->addSql('ALTER TABLE periodic_transfer_transaction DROP FOREIGN KEY FK_FD94E2DECA9A39BB');
        $this->addSql('ALTER TABLE periodic_withdrawal_transaction DROP FOREIGN KEY FK_235BFF36CA9A39BB');
        $this->addSql('ALTER TABLE transfer_transaction DROP FOREIGN KEY FK_AB3B9233CA9A39BB');
        $this->addSql('ALTER TABLE withdrawal_transaction DROP FOREIGN KEY FK_988E5504CA9A39BB');
        $this->addSql('ALTER TABLE deposit_transaction DROP FOREIGN KEY FK_907A74261AD77110');
        $this->addSql('ALTER TABLE transfer_transaction DROP FOREIGN KEY FK_AB3B92337A7C1BC4');
        $this->addSql('ALTER TABLE withdrawal_transaction DROP FOREIGN KEY FK_988E5504293A293F');
        $this->addSql('ALTER TABLE deposit_transaction DROP FOREIGN KEY FK_907A7426953C1C61');
        $this->addSql('ALTER TABLE periodic_deposit_transaction DROP FOREIGN KEY FK_8CCEBA13953C1C61');
        $this->addSql('ALTER TABLE api_token DROP FOREIGN KEY FK_7BA2F5EBA76ED395');
        $this->addSql('ALTER TABLE household_user DROP FOREIGN KEY FK_8CCC41A8A76ED395');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE user_profile DROP FOREIGN KEY FK_D95AB405A76ED395');
        $this->addSql('DROP TABLE account_holder');
        $this->addSql('DROP TABLE api_token');
        $this->addSql('DROP TABLE asset_account');
        $this->addSql('DROP TABLE asset_account_household_user');
        $this->addSql('DROP TABLE booking_category');
        $this->addSql('DROP TABLE deposit_transaction');
        $this->addSql('DROP TABLE expense_account');
        $this->addSql('DROP TABLE household');
        $this->addSql('DROP TABLE household_user');
        $this->addSql('DROP TABLE periodic_deposit_transaction');
        $this->addSql('DROP TABLE periodic_transfer_transaction');
        $this->addSql('DROP TABLE periodic_withdrawal_transaction');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE revenue_account');
        $this->addSql('DROP TABLE transfer_transaction');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_profile');
        $this->addSql('DROP TABLE withdrawal_transaction');
    }
}
