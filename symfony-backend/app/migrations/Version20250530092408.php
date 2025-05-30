<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250530092408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE test_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE test_session_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE test (id INT NOT NULL, session_id INT NOT NULL, arbitro_id INT NOT NULL, categoria_id INT NOT NULL, nota DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D87F7E0C613FECDF ON test (session_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D87F7E0C66FE4594 ON test (arbitro_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D87F7E0C3397707A ON test (categoria_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE test_session (id INT NOT NULL, categoria_id INT NOT NULL, fecha TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, test_number INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C05011C3397707A ON test_session (categoria_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN test_session.fecha IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test ADD CONSTRAINT FK_D87F7E0C613FECDF FOREIGN KEY (session_id) REFERENCES test_session (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test ADD CONSTRAINT FK_D87F7E0C66FE4594 FOREIGN KEY (arbitro_id) REFERENCES arbitros (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test ADD CONSTRAINT FK_D87F7E0C3397707A FOREIGN KEY (categoria_id) REFERENCES categorias (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test_session ADD CONSTRAINT FK_C05011C3397707A FOREIGN KEY (categoria_id) REFERENCES categorias (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE test_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE test_session_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test DROP CONSTRAINT FK_D87F7E0C613FECDF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test DROP CONSTRAINT FK_D87F7E0C66FE4594
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test DROP CONSTRAINT FK_D87F7E0C3397707A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test_session DROP CONSTRAINT FK_C05011C3397707A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE test
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE test_session
        SQL);
    }
}
