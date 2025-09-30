<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250930121605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea la tabla historico relacionada con user (ganador)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE historico (
            id INT AUTO_INCREMENT NOT NULL,
            ganador_id INT NOT NULL,
            nombre_actividad VARCHAR(255) NOT NULL,
            fecha DATETIME NOT NULL,
            INDEX IDX_HISTORICO_GANADOR (ganador_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE historico 
            ADD CONSTRAINT FK_HISTORICO_GANADOR FOREIGN KEY (ganador_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE historico DROP FOREIGN KEY FK_HISTORICO_GANADOR');
        $this->addSql('DROP TABLE historico');
    }
}
