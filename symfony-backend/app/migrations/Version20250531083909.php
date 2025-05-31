<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250531083909 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE tecnico_session_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE tecnicos_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tecnico_session (id INT NOT NULL, categoria_id INT NOT NULL, fecha DATE NOT NULL, exam_number INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_726AE6913397707A ON tecnico_session (categoria_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN tecnico_session.fecha IS '(DC2Type:date_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tecnicos (id INT NOT NULL, session_id INT NOT NULL, arbitro_id INT NOT NULL, nota DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1B020D87613FECDF ON tecnicos (session_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1B020D8766FE4594 ON tecnicos (arbitro_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tecnico_session ADD CONSTRAINT FK_726AE6913397707A FOREIGN KEY (categoria_id) REFERENCES categorias (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tecnicos ADD CONSTRAINT FK_1B020D87613FECDF FOREIGN KEY (session_id) REFERENCES tecnico_session (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tecnicos ADD CONSTRAINT FK_1B020D8766FE4594 FOREIGN KEY (arbitro_id) REFERENCES arbitros (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE tecnico_session_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE tecnicos_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tecnico_session DROP CONSTRAINT FK_726AE6913397707A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tecnicos DROP CONSTRAINT FK_1B020D87613FECDF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tecnicos DROP CONSTRAINT FK_1B020D8766FE4594
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tecnico_session
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tecnicos
        SQL);
    }
}
