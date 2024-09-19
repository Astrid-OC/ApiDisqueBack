<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919092950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chansons ADD chanteurs_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE chansons ADD CONSTRAINT FK_6EC68AA0AD69FA37 FOREIGN KEY (chanteurs_id) REFERENCES chanteur (id)');
        $this->addSql('CREATE INDEX IDX_6EC68AA0AD69FA37 ON chansons (chanteurs_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chansons DROP FOREIGN KEY FK_6EC68AA0AD69FA37');
        $this->addSql('DROP INDEX IDX_6EC68AA0AD69FA37 ON chansons');
        $this->addSql('ALTER TABLE chansons DROP chanteurs_id');
    }
}
