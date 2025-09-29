<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250925100552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participante ADD sorteo_id INT NOT NULL');
        $this->addSql('ALTER TABLE participante ADD CONSTRAINT FK_85BDC5C3663FD436 FOREIGN KEY (sorteo_id) REFERENCES sorteo (id)');
        $this->addSql('CREATE INDEX IDX_85BDC5C3663FD436 ON participante (sorteo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participante DROP FOREIGN KEY FK_85BDC5C3663FD436');
        $this->addSql('DROP INDEX IDX_85BDC5C3663FD436 ON participante');
        $this->addSql('ALTER TABLE participante DROP sorteo_id');
    }
}
