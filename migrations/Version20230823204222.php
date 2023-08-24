<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230823204222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE linked_acount (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, type_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD linked_acount_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6499839ABA1 FOREIGN KEY (linked_acount_id) REFERENCES linked_acount (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6499839ABA1 ON user (linked_acount_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D6499839ABA1');
        $this->addSql('DROP TABLE linked_acount');
        $this->addSql('DROP INDEX IDX_8D93D6499839ABA1 ON `user`');
        $this->addSql('ALTER TABLE `user` DROP linked_acount_id');
    }
}
