<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250526181836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed inicial: categorías con ids fijos y admin con id fijo';
    }

    public function up(Schema $schema): void
    {
        // 1) Creamos las categorías con IDs concretos
        $this->addSql(<<<'SQL'
            INSERT INTO categorias (id, name)
            VALUES 
              (1, 'Oficial'),
              (2, 'Auxiliar'),
              (3, 'Provincial')
            ON CONFLICT (id) DO NOTHING
        SQL);

        // 2) Creamos el admin con ID = 1
        $this->addSql(<<<'SQL'
            INSERT INTO "user" (id, username, password, roles, email)
            VALUES (
              1,
              'admin',
              '$2y$13$INp4OgXLo3W8ItsJjEK6DO0ozubXIzOinUrtI.igCoQ4K7vfBmm3e',
              '["ROLE_ADMIN"]',
              'admin@correo.com'
            )
            ON CONFLICT (id) DO NOTHING
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Eliminamos sólo los seeds
        $this->addSql("DELETE FROM categorias WHERE id IN (1,2,3);");
        $this->addSql("DELETE FROM \"user\" WHERE id = 1;");
    }
}
