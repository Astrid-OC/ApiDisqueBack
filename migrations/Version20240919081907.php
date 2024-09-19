<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919081907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chanteur (id INT AUTO_INCREMENT NOT NULL, nom_chanteur VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE disque ADD chanteur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE disque ADD CONSTRAINT FK_F5ACECA2C7E25364 FOREIGN KEY (chanteur_id) REFERENCES chanteur (id)');
        $this->addSql('CREATE INDEX IDX_F5ACECA2C7E25364 ON disque (chanteur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE disque DROP FOREIGN KEY FK_F5ACECA2C7E25364');
        $this->addSql('DROP TABLE chanteur');
        $this->addSql('DROP INDEX IDX_F5ACECA2C7E25364 ON disque');
        $this->addSql('ALTER TABLE disque DROP chanteur_id');
    }
}
