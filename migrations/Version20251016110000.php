<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add numero_ganadores to sorteo and puesto to participante/historico
 */
final class Version20251016110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add numero_ganadores to sorteo; add puesto to participante and historico';
    }

    public function up(Schema $schema): void
    {
        // numero_ganadores: not null with default 1 to satisfy existing rows
        $this->addSql('ALTER TABLE sorteo ADD numero_ganadores INT NOT NULL DEFAULT 1');

        // puesto columns are optional (nullable)
        $this->addSql('ALTER TABLE participante ADD puesto INT DEFAULT NULL');
        $this->addSql('ALTER TABLE historico ADD puesto INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE historico DROP puesto');
        $this->addSql('ALTER TABLE participante DROP puesto');
        $this->addSql('ALTER TABLE sorteo DROP numero_ganadores');
    }
}