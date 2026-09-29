<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Crea la tabla contrato_template (plantillas de contrato editables por
 * empresa y tipo de cliente) y registra el módulo para asignar privilegios
 * y agregarlo a un menú desde las pantallas existentes de Módulo/Menu.
 */
final class Version20260914120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea contrato_template y registra el módulo contrato_template';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contrato_template (id INT AUTO_INCREMENT NOT NULL, empresa_id INT NOT NULL, tipo_cliente_id INT NOT NULL, usuario_registro_id INT DEFAULT NULL, nombre VARCHAR(150) NOT NULL, contenido LONGTEXT NOT NULL, activo TINYINT(1) NOT NULL, fecha_creacion DATETIME NOT NULL, fecha_modificacion DATETIME DEFAULT NULL, INDEX IDX_A2E2F5C1521E1991 (empresa_id), INDEX IDX_A2E2F5C1AA53A244 (tipo_cliente_id), INDEX IDX_A2E2F5C11EEFD20 (usuario_registro_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contrato_template ADD CONSTRAINT FK_A2E2F5C1521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id)');
        $this->addSql('ALTER TABLE contrato_template ADD CONSTRAINT FK_A2E2F5C1AA53A244 FOREIGN KEY (tipo_cliente_id) REFERENCES tipo_cliente (id)');
        $this->addSql('ALTER TABLE contrato_template ADD CONSTRAINT FK_A2E2F5C11EEFD20 FOREIGN KEY (usuario_registro_id) REFERENCES usuario (id)');

        // Igual que cada módulo existente (ver tabla modulo): 'nombre' es el
        // string que usan denyAccessUnlessGranted() y PrincipalVoter, 'ruta' es
        // la ruta de su index. Falta, fuera de esta migración, entrar a
        // /modulo/new (crea el ModuloPer de cada empresa) y dar privilegios y
        // un ítem de Menu desde las pantallas ya existentes.
        $this->addSql("INSERT INTO modulo (nombre, ruta, nombre_alt, descripcion) VALUES ('contrato_template', 'contrato_template_index', 'Plantillas de Contrato', 'Plantillas de contrato editables por tipo de cliente')");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("DELETE FROM modulo_per WHERE modulo_id IN (SELECT id FROM modulo WHERE nombre = 'contrato_template')");
        $this->addSql("DELETE FROM modulo WHERE nombre = 'contrato_template'");
        $this->addSql('ALTER TABLE contrato_template DROP FOREIGN KEY FK_A2E2F5C1521E1991');
        $this->addSql('ALTER TABLE contrato_template DROP FOREIGN KEY FK_A2E2F5C1AA53A244');
        $this->addSql('ALTER TABLE contrato_template DROP FOREIGN KEY FK_A2E2F5C11EEFD20');
        $this->addSql('DROP TABLE contrato_template');
    }
}
