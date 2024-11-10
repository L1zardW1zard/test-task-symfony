<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241104011159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD roles JSON NOT NULL, CHANGE id id VARCHAR(255) NOT NULL, CHANGE login login VARCHAR(8) NOT NULL, CHANGE phone phone VARCHAR(20) NOT NULL, CHANGE pass pass VARCHAR(8) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649AA08CB10 ON user (login)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8D93D649AA08CB10 ON user');
        $this->addSql('ALTER TABLE user DROP roles, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE login login VARCHAR(255) NOT NULL, CHANGE phone phone VARCHAR(32) DEFAULT NULL, CHANGE pass pass VARCHAR(255) NOT NULL');
    }
}
