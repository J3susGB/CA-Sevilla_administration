<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528124443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE clase_sesion ADD categoria_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clase_sesion ADD CONSTRAINT FK_6ECD980F3397707A FOREIGN KEY (categoria_id) REFERENCES categorias (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6ECD980F3397707A ON clase_sesion (categoria_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clase_sesion DROP CONSTRAINT FK_6ECD980F3397707A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6ECD980F3397707A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clase_sesion DROP categoria_id
        SQL);
    }
}
