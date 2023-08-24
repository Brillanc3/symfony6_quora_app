<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230823235857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_12F8AD738CDE5729 ON linked_acount');
        $this->addSql('DROP INDEX UNIQ_12F8AD738D93D649 ON linked_acount');
        $this->addSql('ALTER TABLE linked_acount CHANGE user user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE linked_acount ADD CONSTRAINT FK_12F8AD73A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_12F8AD73A76ED395 ON linked_acount (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE linked_acount DROP FOREIGN KEY FK_12F8AD73A76ED395');
        $this->addSql('DROP INDEX IDX_12F8AD73A76ED395 ON linked_acount');
        $this->addSql('ALTER TABLE linked_acount CHANGE user_id user INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_12F8AD738CDE5729 ON linked_acount (type)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_12F8AD738D93D649 ON linked_acount (user)');
    }
}
