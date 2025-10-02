<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251002091101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prescription ADD id_member_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D9F5A26FD9 FOREIGN KEY (id_member_id) REFERENCES `member` (id)');
        $this->addSql('CREATE INDEX IDX_1FBFB8D9F5A26FD9 ON prescription (id_member_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D9F5A26FD9');
        $this->addSql('DROP INDEX IDX_1FBFB8D9F5A26FD9 ON prescription');
        $this->addSql('ALTER TABLE prescription DROP id_member_id');
    }
}
