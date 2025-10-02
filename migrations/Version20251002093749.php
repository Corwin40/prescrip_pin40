<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251002093749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prescription ADD id_equipment_id INT NOT NULL');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D972BE9C8C FOREIGN KEY (id_equipment_id) REFERENCES equipment (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FBFB8D972BE9C8C ON prescription (id_equipment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D972BE9C8C');
        $this->addSql('DROP INDEX UNIQ_1FBFB8D972BE9C8C ON prescription');
        $this->addSql('ALTER TABLE prescription DROP id_equipment_id');
    }
}
