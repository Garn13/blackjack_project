<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230308165320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hand ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE hand ADD CONSTRAINT FK_2762428FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2762428FA76ED395 ON hand (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hand DROP FOREIGN KEY FK_2762428FA76ED395');
        $this->addSql('DROP INDEX IDX_2762428FA76ED395 ON hand');
        $this->addSql('ALTER TABLE hand DROP user_id');
    }
}
