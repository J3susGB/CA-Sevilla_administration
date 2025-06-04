<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250604085407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE fisica_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE fisica (id INT NOT NULL, arbitro_id INT NOT NULL, categoria_id INT NOT NULL, convocatoria INT NOT NULL, repesca BOOLEAN DEFAULT false, yoyo DOUBLE PRECISION NOT NULL, velocidad DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B1A4CEAF66FE4594 ON fisica (arbitro_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B1A4CEAF3397707A ON fisica (categoria_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fisica ADD CONSTRAINT FK_B1A4CEAF66FE4594 FOREIGN KEY (arbitro_id) REFERENCES arbitros (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fisica ADD CONSTRAINT FK_B1A4CEAF3397707A FOREIGN KEY (categoria_id) REFERENCES categorias (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE fisica_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fisica DROP CONSTRAINT FK_B1A4CEAF66FE4594
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fisica DROP CONSTRAINT FK_B1A4CEAF3397707A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE fisica
        SQL);
    }
}
