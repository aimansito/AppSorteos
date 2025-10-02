<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251002064558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historico DROP FOREIGN KEY fk_historico_sorteo');
        $this->addSql('ALTER TABLE historico DROP FOREIGN KEY FK_GANADOR_PARTICIPANTE_ID');
        $this->addSql('ALTER TABLE historico DROP FOREIGN KEY fk_historico_sorteo');
        $this->addSql('ALTER TABLE historico ADD CONSTRAINT FK_8DAA356A663FD436 FOREIGN KEY (sorteo_id) REFERENCES sorteo (id)');
        $this->addSql('DROP INDEX idx_historico_ganador ON historico');
        $this->addSql('CREATE INDEX IDX_8DAA356AA338CEA5 ON historico (ganador_id)');
        $this->addSql('DROP INDEX idx_historico_sorteo ON historico');
        $this->addSql('CREATE INDEX IDX_8DAA356A663FD436 ON historico (sorteo_id)');
        $this->addSql('ALTER TABLE historico ADD CONSTRAINT FK_GANADOR_PARTICIPANTE_ID FOREIGN KEY (ganador_id) REFERENCES participante (id)');
        $this->addSql('ALTER TABLE historico ADD CONSTRAINT fk_historico_sorteo FOREIGN KEY (sorteo_id) REFERENCES sorteo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participante DROP FOREIGN KEY FK_PARTICIPANTE_SORTEO');
        $this->addSql('ALTER TABLE participante DROP FOREIGN KEY FK_PARTICIPANTE_SORTEO');
        $this->addSql('ALTER TABLE participante CHANGE es_ganador es_ganador TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE participante ADD CONSTRAINT FK_85BDC5C3663FD436 FOREIGN KEY (sorteo_id) REFERENCES sorteo (id)');
        $this->addSql('DROP INDEX idx_participante_sorteo ON participante');
        $this->addSql('CREATE INDEX IDX_85BDC5C3663FD436 ON participante (sorteo_id)');
        $this->addSql('ALTER TABLE participante ADD CONSTRAINT FK_PARTICIPANTE_SORTEO FOREIGN KEY (sorteo_id) REFERENCES sorteo (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX uniq_identifier_email ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON user (username)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historico DROP FOREIGN KEY FK_8DAA356A663FD436');
        $this->addSql('ALTER TABLE historico DROP FOREIGN KEY FK_8DAA356AA338CEA5');
        $this->addSql('ALTER TABLE historico DROP FOREIGN KEY FK_8DAA356A663FD436');
        $this->addSql('ALTER TABLE historico ADD CONSTRAINT fk_historico_sorteo FOREIGN KEY (sorteo_id) REFERENCES sorteo (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_8daa356aa338cea5 ON historico');
        $this->addSql('CREATE INDEX idx_historico_ganador ON historico (ganador_id)');
        $this->addSql('DROP INDEX idx_8daa356a663fd436 ON historico');
        $this->addSql('CREATE INDEX idx_historico_sorteo ON historico (sorteo_id)');
        $this->addSql('ALTER TABLE historico ADD CONSTRAINT FK_8DAA356AA338CEA5 FOREIGN KEY (ganador_id) REFERENCES participante (id)');
        $this->addSql('ALTER TABLE historico ADD CONSTRAINT FK_8DAA356A663FD436 FOREIGN KEY (sorteo_id) REFERENCES sorteo (id)');
        $this->addSql('ALTER TABLE participante DROP FOREIGN KEY FK_85BDC5C3663FD436');
        $this->addSql('ALTER TABLE participante DROP FOREIGN KEY FK_85BDC5C3663FD436');
        $this->addSql('ALTER TABLE participante CHANGE es_ganador es_ganador TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE participante ADD CONSTRAINT FK_PARTICIPANTE_SORTEO FOREIGN KEY (sorteo_id) REFERENCES sorteo (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_85bdc5c3663fd436 ON participante');
        $this->addSql('CREATE INDEX IDX_PARTICIPANTE_SORTEO ON participante (sorteo_id)');
        $this->addSql('ALTER TABLE participante ADD CONSTRAINT FK_85BDC5C3663FD436 FOREIGN KEY (sorteo_id) REFERENCES sorteo (id)');
        $this->addSql('DROP INDEX uniq_identifier_username ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (username)');
    }
}
