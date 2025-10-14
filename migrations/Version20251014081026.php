<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251014081026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE historico (id INT AUTO_INCREMENT NOT NULL, ganador_id INT NOT NULL, sorteo_id INT NOT NULL, nombre_actividad VARCHAR(255) NOT NULL, fecha DATETIME NOT NULL, INDEX IDX_8DAA356AA338CEA5 (ganador_id), INDEX IDX_8DAA356A663FD436 (sorteo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participante (id INT AUTO_INCREMENT NOT NULL, sorteo_id INT NOT NULL, nombre VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, codigo_entrada VARCHAR(255) NOT NULL, es_ganador TINYINT(1) NOT NULL, INDEX IDX_85BDC5C3663FD436 (sorteo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sorteo (id INT AUTO_INCREMENT NOT NULL, nombre_actividad VARCHAR(255) NOT NULL, fecha DATETIME NOT NULL, lugar VARCHAR(255) NOT NULL, max_participantes INT NOT NULL, imagen VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE historico ADD CONSTRAINT FK_8DAA356AA338CEA5 FOREIGN KEY (ganador_id) REFERENCES participante (id)');
        $this->addSql('ALTER TABLE historico ADD CONSTRAINT FK_8DAA356A663FD436 FOREIGN KEY (sorteo_id) REFERENCES sorteo (id)');
        $this->addSql('ALTER TABLE participante ADD CONSTRAINT FK_85BDC5C3663FD436 FOREIGN KEY (sorteo_id) REFERENCES sorteo (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historico DROP FOREIGN KEY FK_8DAA356AA338CEA5');
        $this->addSql('ALTER TABLE historico DROP FOREIGN KEY FK_8DAA356A663FD436');
        $this->addSql('ALTER TABLE participante DROP FOREIGN KEY FK_85BDC5C3663FD436');
        $this->addSql('DROP TABLE historico');
        $this->addSql('DROP TABLE participante');
        $this->addSql('DROP TABLE sorteo');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
