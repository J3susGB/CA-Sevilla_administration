<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250604180546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE observaciones_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE observaciones (id INT NOT NULL, categoria_id INT NOT NULL, codigo VARCHAR(10) NOT NULL, descripcion TEXT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C1F3A27E3397707A ON observaciones (categoria_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE observaciones ADD CONSTRAINT FK_C1F3A27E3397707A FOREIGN KEY (categoria_id) REFERENCES categorias (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE observaciones_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE observaciones DROP CONSTRAINT FK_C1F3A27E3397707A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE observaciones
        SQL);
    }
}
