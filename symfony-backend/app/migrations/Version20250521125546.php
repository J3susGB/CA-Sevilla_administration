<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521125546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE arbitros ADD categoria_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE arbitros ADD CONSTRAINT FK_4BCBD9383397707A FOREIGN KEY (categoria_id) REFERENCES categorias (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4BCBD9383397707A ON arbitros (categoria_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorias DROP CONSTRAINT fk_5e9f836c66fe4594
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_5e9f836c66fe4594
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorias DROP arbitro_id
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorias ADD arbitro_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorias ADD CONSTRAINT fk_5e9f836c66fe4594 FOREIGN KEY (arbitro_id) REFERENCES arbitros (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_5e9f836c66fe4594 ON categorias (arbitro_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE arbitros DROP CONSTRAINT FK_4BCBD9383397707A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4BCBD9383397707A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE arbitros DROP categoria_id
        SQL);
    }
}
