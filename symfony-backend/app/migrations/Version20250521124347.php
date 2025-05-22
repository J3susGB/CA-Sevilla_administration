<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521124347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE arbitros_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE categorias_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE arbitros (id INT NOT NULL, name VARCHAR(255) NOT NULL, first_surname VARCHAR(255) NOT NULL, second_surname VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE categorias (id INT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5E9F836C12469DE2 ON categorias (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorias ADD CONSTRAINT FK_5E9F836C12469DE2 FOREIGN KEY (category_id) REFERENCES arbitros (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE arbitros_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE categorias_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorias DROP CONSTRAINT FK_5E9F836C12469DE2
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE arbitros
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE categorias
        SQL);
    }
}
