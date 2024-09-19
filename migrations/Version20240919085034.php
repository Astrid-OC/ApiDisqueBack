<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919085034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chansons (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, duree INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chansons_disque (chansons_id INT NOT NULL, disque_id INT NOT NULL, INDEX IDX_49B372BAC528F7A (chansons_id), INDEX IDX_49B372BA91161FE8 (disque_id), PRIMARY KEY(chansons_id, disque_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chansons_disque ADD CONSTRAINT FK_49B372BAC528F7A FOREIGN KEY (chansons_id) REFERENCES chansons (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chansons_disque ADD CONSTRAINT FK_49B372BA91161FE8 FOREIGN KEY (disque_id) REFERENCES disque (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chansons_disque DROP FOREIGN KEY FK_49B372BAC528F7A');
        $this->addSql('ALTER TABLE chansons_disque DROP FOREIGN KEY FK_49B372BA91161FE8');
        $this->addSql('DROP TABLE chansons');
        $this->addSql('DROP TABLE chansons_disque');
    }
}
