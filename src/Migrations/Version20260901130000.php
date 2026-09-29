<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Parte 3 · Etapa 1 — Usuario con FK directa a Empresa (membresia real).
 *
 * Backfill de usuario.empresa_id:
 *   1. usuario.empresa_actual, si apunta a una empresa que existe.
 *   2. si no, la empresa de su primera cuenta asignada (usuario_cuenta -> cuenta).
 *
 * Se deja NULLABLE por ahora (transicion). getEmpresaActual() cae al valor
 * historico si empresa_id quedara NULL.
 */
final class Version20260901130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'usuario.empresa_id (membresia directa Usuario->Empresa) + backfill';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE usuario ADD empresa_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_2265B05D521E1991 ON usuario (empresa_id)');
        $this->addSql('ALTER TABLE usuario ADD CONSTRAINT FK_2265B05D521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id)');

        // 1) desde empresa_actual si es una empresa real
        $this->addSql('
            UPDATE usuario u
            INNER JOIN empresa e ON e.id = u.empresa_actual
            SET u.empresa_id = e.id
            WHERE u.empresa_id IS NULL
        ');

        // 2) resto: desde la primera cuenta asignada
        $this->addSql('
            UPDATE usuario u
            INNER JOIN (
                SELECT uc.usuario_id, MIN(c.empresa_id) AS empresa_id
                FROM usuario_cuenta uc
                INNER JOIN cuenta c ON c.id = uc.cuenta_id
                GROUP BY uc.usuario_id
            ) x ON x.usuario_id = u.id
            SET u.empresa_id = x.empresa_id
            WHERE u.empresa_id IS NULL
        ');

        $sinEmpresa = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM usuario WHERE empresa_id IS NULL AND estado = 1');
        if ($sinEmpresa > 0) {
            $this->write(sprintf(
                '  [aviso] %d usuario(s) activos quedaron sin empresa_id (sin empresa_actual valido ni cuenta asignada). '
                . 'Revisar: SELECT id, username FROM usuario WHERE empresa_id IS NULL AND estado = 1;',
                $sinEmpresa
            ));
        }
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE usuario DROP FOREIGN KEY FK_2265B05D521E1991');
        $this->addSql('DROP INDEX IDX_2265B05D521E1991 ON usuario');
        $this->addSql('ALTER TABLE usuario DROP empresa_id');
    }
}
