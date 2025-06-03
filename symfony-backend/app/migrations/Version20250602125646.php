<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250602125646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE entrenamientos_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE entrenamientos (id INT NOT NULL, arbitro_id INT NOT NULL, categoria_id INT NOT NULL, septiembre INT DEFAULT 0 NOT NULL, octubre INT DEFAULT 0 NOT NULL, noviembre INT DEFAULT 0 NOT NULL, diciembre INT DEFAULT 0 NOT NULL, enero INT DEFAULT 0 NOT NULL, febrero INT DEFAULT 0 NOT NULL, marzo INT DEFAULT 0 NOT NULL, abril INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_24DCB62B66FE4594 ON entrenamientos (arbitro_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_24DCB62B3397707A ON entrenamientos (categoria_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entrenamientos ADD CONSTRAINT FK_24DCB62B66FE4594 FOREIGN KEY (arbitro_id) REFERENCES arbitros (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entrenamientos ADD CONSTRAINT FK_24DCB62B3397707A FOREIGN KEY (categoria_id) REFERENCES categorias (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE entrenamientos_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entrenamientos DROP CONSTRAINT FK_24DCB62B66FE4594
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entrenamientos DROP CONSTRAINT FK_24DCB62B3397707A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE entrenamientos
        SQL);
    }
}
