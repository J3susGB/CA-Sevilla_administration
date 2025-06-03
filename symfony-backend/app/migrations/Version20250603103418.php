<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603103418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE simulacros_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE simulacros (id INT NOT NULL, arbitro_id INT NOT NULL, categoria_id INT NOT NULL, fecha DATE NOT NULL, periodo DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7561091566FE4594 ON simulacros (arbitro_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_756109153397707A ON simulacros (categoria_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE simulacros ADD CONSTRAINT FK_7561091566FE4594 FOREIGN KEY (arbitro_id) REFERENCES arbitros (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE simulacros ADD CONSTRAINT FK_756109153397707A FOREIGN KEY (categoria_id) REFERENCES categorias (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE simulacros_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE simulacros DROP CONSTRAINT FK_7561091566FE4594
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE simulacros DROP CONSTRAINT FK_756109153397707A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE simulacros
        SQL);
    }
}
