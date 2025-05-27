<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527090903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE bonificaciones_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE bonificaciones (id INT NOT NULL, categoria_id INT NOT NULL, name VARCHAR(255) NOT NULL, valor NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CA255F913397707A ON bonificaciones (categoria_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bonificaciones ADD CONSTRAINT FK_CA255F913397707A FOREIGN KEY (categoria_id) REFERENCES categorias (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE bonificaciones_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bonificaciones DROP CONSTRAINT FK_CA255F913397707A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bonificaciones
        SQL);
    }
}
