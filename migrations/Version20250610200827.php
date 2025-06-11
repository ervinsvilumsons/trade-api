<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250610200827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE account (id SERIAL NOT NULL, client_id INT NOT NULL, currency VARCHAR(3) NOT NULL, balance NUMERIC(15, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7D3656A419EB6921 ON account (client_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN account.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE client (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN client.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE transaction (id SERIAL NOT NULL, from_account_id INT NOT NULL, to_account_id INT NOT NULL, original_amount NUMERIC(15, 2) NOT NULL, converted_amount NUMERIC(15, 2) DEFAULT NULL, from_currency VARCHAR(3) NOT NULL, to_currency VARCHAR(3) DEFAULT NULL, exchange_rate NUMERIC(15, 6) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_723705D1B0CF99BD ON transaction (from_account_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_723705D1BC58BDC7 ON transaction (to_account_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN transaction.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE account ADD CONSTRAINT FK_7D3656A419EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD CONSTRAINT FK_723705D1B0CF99BD FOREIGN KEY (from_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD CONSTRAINT FK_723705D1BC58BDC7 FOREIGN KEY (to_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE account DROP CONSTRAINT FK_7D3656A419EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP CONSTRAINT FK_723705D1B0CF99BD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP CONSTRAINT FK_723705D1BC58BDC7
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE account
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE client
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE transaction
        SQL);
    }
}
