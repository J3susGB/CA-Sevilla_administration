<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521125048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE categorias DROP CONSTRAINT fk_5e9f836c12469de2
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_5e9f836c12469de2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorias RENAME COLUMN category_id TO arbitro_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorias ADD CONSTRAINT FK_5E9F836C66FE4594 FOREIGN KEY (arbitro_id) REFERENCES arbitros (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5E9F836C66FE4594 ON categorias (arbitro_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorias DROP CONSTRAINT FK_5E9F836C66FE4594
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_5E9F836C66FE4594
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorias RENAME COLUMN arbitro_id TO category_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorias ADD CONSTRAINT fk_5e9f836c12469de2 FOREIGN KEY (category_id) REFERENCES arbitros (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_5e9f836c12469de2 ON categorias (category_id)
        SQL);
    }
}
