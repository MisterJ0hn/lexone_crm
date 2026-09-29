<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Separa la materia del contrato y la lleva a la causa; elimina Cartera y CuentaMateria.
 *
 *  - causa.materia_id: nueva FK obligatoria. Backfill desde
 *    causa.materia_estrategia -> materia y, para el resto, desde
 *    agenda -> cuenta -> cuenta_materia(estado=1).
 *  - Se eliminan las tablas cartera y usuario_cartera, y las columnas
 *    contrato.cartera_id / contrato.cartera_orden (la cobranza ya no se
 *    particiona por cartera; el tramitador sale de la ecuación).
 *  - Se elimina la tabla cuenta_materia: la materia pertenece sólo a la Empresa.
 */
final class Version20260901120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Materia por causa; eliminar Cartera, UsuarioCartera y CuentaMateria';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        // ---------------------------------------------------------------
        // 1) causa.materia_id (obligatoria)
        // ---------------------------------------------------------------
        $this->addSql('ALTER TABLE causa ADD materia_id INT DEFAULT NULL');

        // 1a) desde la materiaEstrategia (servicio) ya elegida
        $this->addSql('
            UPDATE causa c
            INNER JOIN materia_estrategia me ON c.materia_estrategia_id = me.id
            SET c.materia_id = me.materia_id
            WHERE c.materia_id IS NULL
        ');

        // 1b) resto: desde la agenda -> cuenta -> cuenta_materia activa
        $this->addSql('
            UPDATE causa c
            INNER JOIN agenda a ON c.agenda_id = a.id
            INNER JOIN cuenta_materia cm ON cm.cuenta_id = a.cuenta_id AND cm.estado = 1
            SET c.materia_id = cm.materia_id
            WHERE c.materia_id IS NULL
        ');

        $huerfanas = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM causa WHERE materia_id IS NULL');
        $this->abortIf(
            $huerfanas > 0,
            sprintf(
                'Hay %d causa(s) sin materia asignable automáticamente. Revisar: '
                . 'SELECT c.id, c.agenda_id FROM causa c WHERE c.materia_id IS NULL;  '
                . 'Asignar materia_id manualmente y volver a ejecutar.',
                $huerfanas
            )
        );

        $this->addSql('CREATE INDEX IDX_F7B7BBA6B54DBBCB ON causa (materia_id)');
        $this->addSql('ALTER TABLE causa ADD CONSTRAINT FK_F7B7BBA6B54DBBCB FOREIGN KEY (materia_id) REFERENCES materia (id)');
        $this->addSql('ALTER TABLE causa MODIFY materia_id INT NOT NULL');

        // ---------------------------------------------------------------
        // 2) Eliminar Cartera / UsuarioCartera y sus columnas en contrato
        // ---------------------------------------------------------------
        $this->addSql('ALTER TABLE usuario_cartera DROP FOREIGN KEY FK_935338ABA5A18135');
        $this->addSql('ALTER TABLE usuario_cartera DROP FOREIGN KEY FK_935338ABDB38439E');
        $this->addSql('DROP TABLE usuario_cartera');

        $this->addSql('ALTER TABLE contrato DROP FOREIGN KEY FK_66696523A5A18135');
        $this->addSql('DROP INDEX IDX_66696523A5A18135 ON contrato');
        $this->addSql('ALTER TABLE contrato DROP cartera_id, DROP cartera_orden');

        $this->addSql('ALTER TABLE cartera DROP FOREIGN KEY FK_EA7592ECB54DBBCB');
        $this->addSql('DROP TABLE cartera');

        // ---------------------------------------------------------------
        // 3) Eliminar CuentaMateria
        // ---------------------------------------------------------------
        $this->addSql('ALTER TABLE cuenta_materia DROP FOREIGN KEY FK_FCAF66119AEFF118');
        $this->addSql('ALTER TABLE cuenta_materia DROP FOREIGN KEY FK_FCAF6611B54DBBCB');
        $this->addSql('DROP TABLE cuenta_materia');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        // Nota: los datos de cartera / usuario_cartera / cuenta_materia y de
        // contrato.cartera_* se pierden; el down() sólo recrea la estructura.

        $this->addSql('CREATE TABLE cuenta_materia (id INT AUTO_INCREMENT NOT NULL, cuenta_id INT NOT NULL, materia_id INT NOT NULL, estado TINYINT(1) DEFAULT NULL, INDEX IDX_FCAF66119AEFF118 (cuenta_id), INDEX IDX_FCAF6611B54DBBCB (materia_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cuenta_materia ADD CONSTRAINT FK_FCAF66119AEFF118 FOREIGN KEY (cuenta_id) REFERENCES cuenta (id)');
        $this->addSql('ALTER TABLE cuenta_materia ADD CONSTRAINT FK_FCAF6611B54DBBCB FOREIGN KEY (materia_id) REFERENCES materia (id)');

        $this->addSql('CREATE TABLE cartera (id INT AUTO_INCREMENT NOT NULL, materia_id INT NOT NULL, nombre VARCHAR(20) NOT NULL, estado TINYINT(1) NOT NULL, orden INT NOT NULL, utilizado TINYINT(1) NOT NULL, asignado TINYINT(1) NOT NULL, INDEX IDX_EA7592ECB54DBBCB (materia_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cartera ADD CONSTRAINT FK_EA7592ECB54DBBCB FOREIGN KEY (materia_id) REFERENCES materia (id)');

        $this->addSql('CREATE TABLE usuario_cartera (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, cartera_id INT NOT NULL, INDEX IDX_935338ABDB38439E (usuario_id), INDEX IDX_935338ABA5A18135 (cartera_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE usuario_cartera ADD CONSTRAINT FK_935338ABDB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE usuario_cartera ADD CONSTRAINT FK_935338ABA5A18135 FOREIGN KEY (cartera_id) REFERENCES cartera (id)');

        $this->addSql('ALTER TABLE contrato ADD cartera_id INT DEFAULT NULL, ADD cartera_orden INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_66696523A5A18135 ON contrato (cartera_id)');
        $this->addSql('ALTER TABLE contrato ADD CONSTRAINT FK_66696523A5A18135 FOREIGN KEY (cartera_id) REFERENCES cartera (id)');

        $this->addSql('ALTER TABLE causa DROP FOREIGN KEY FK_F7B7BBA6B54DBBCB');
        $this->addSql('DROP INDEX IDX_F7B7BBA6B54DBBCB ON causa');
        $this->addSql('ALTER TABLE causa DROP materia_id');
    }
}
