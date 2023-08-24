<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230823221145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE linked_acount ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE linked_acount ADD CONSTRAINT FK_12F8AD73A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_12F8AD73A76ED395 ON linked_acount (user_id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6499839ABA1');
        $this->addSql('DROP INDEX IDX_8D93D6499839ABA1 ON user');
        $this->addSql('ALTER TABLE user DROP linked_acount_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE linked_acount DROP FOREIGN KEY FK_12F8AD73A76ED395');
        $this->addSql('DROP INDEX IDX_12F8AD73A76ED395 ON linked_acount');
        $this->addSql('ALTER TABLE linked_acount DROP user_id');
        $this->addSql('ALTER TABLE `user` ADD linked_acount_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D6499839ABA1 FOREIGN KEY (linked_acount_id) REFERENCES linked_acount (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8D93D6499839ABA1 ON `user` (linked_acount_id)');
    }
}
