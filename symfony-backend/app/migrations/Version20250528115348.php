<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528115348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE asistencia_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE clase_sesion_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE asistencia (id INT NOT NULL, sesion_id INT NOT NULL, arbitro_id INT NOT NULL, categoria_id INT NOT NULL, asiste BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D8264A8D1CCCADCB ON asistencia (sesion_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D8264A8D66FE4594 ON asistencia (arbitro_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D8264A8D3397707A ON asistencia (categoria_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE clase_sesion (id INT NOT NULL, fecha DATE NOT NULL, tipo VARCHAR(20) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE asistencia ADD CONSTRAINT FK_D8264A8D1CCCADCB FOREIGN KEY (sesion_id) REFERENCES clase_sesion (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE asistencia ADD CONSTRAINT FK_D8264A8D66FE4594 FOREIGN KEY (arbitro_id) REFERENCES arbitros (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE asistencia ADD CONSTRAINT FK_D8264A8D3397707A FOREIGN KEY (categoria_id) REFERENCES categorias (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE asistencia_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE clase_sesion_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE asistencia DROP CONSTRAINT FK_D8264A8D1CCCADCB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE asistencia DROP CONSTRAINT FK_D8264A8D66FE4594
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE asistencia DROP CONSTRAINT FK_D8264A8D3397707A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE asistencia
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE clase_sesion
        SQL);
    }
}
