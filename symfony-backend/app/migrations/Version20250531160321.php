<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250531160321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE informes_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE informes (id INT NOT NULL, arbitro_id INT NOT NULL, categoria_id INT NOT NULL, fecha TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, nota DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E47FD09A66FE4594 ON informes (arbitro_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E47FD09A3397707A ON informes (categoria_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN informes.fecha IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE informes ADD CONSTRAINT FK_E47FD09A66FE4594 FOREIGN KEY (arbitro_id) REFERENCES arbitros (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE informes ADD CONSTRAINT FK_E47FD09A3397707A FOREIGN KEY (categoria_id) REFERENCES categorias (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE informes_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE informes DROP CONSTRAINT FK_E47FD09A66FE4594
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE informes DROP CONSTRAINT FK_E47FD09A3397707A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE informes
        SQL);
    }
}
