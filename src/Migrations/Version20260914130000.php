<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Agrega a Contrato la plantilla que el usuario elige al crear el contrato
 * (ver PanelAbogadoController::contrata() y ContratoController::pdf()).
 */
final class Version20260914130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Agrega contrato.contrato_template_id';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contrato ADD contrato_template_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contrato ADD CONSTRAINT FK_666965236DF6D5E9 FOREIGN KEY (contrato_template_id) REFERENCES contrato_template (id)');
        $this->addSql('CREATE INDEX IDX_666965236DF6D5E9 ON contrato (contrato_template_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contrato DROP FOREIGN KEY FK_666965236DF6D5E9');
        $this->addSql('DROP INDEX IDX_666965236DF6D5E9 ON contrato');
        $this->addSql('ALTER TABLE contrato DROP contrato_template_id');
    }
}
