<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230308164504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hand ADD game_id INT NOT NULL');
        $this->addSql('ALTER TABLE hand ADD CONSTRAINT FK_2762428FE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('CREATE INDEX IDX_2762428FE48FD905 ON hand (game_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hand DROP FOREIGN KEY FK_2762428FE48FD905');
        $this->addSql('DROP INDEX IDX_2762428FE48FD905 ON hand');
        $this->addSql('ALTER TABLE hand DROP game_id');
    }
}
