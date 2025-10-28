<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251028102523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prescription ADD lieu_mediation_id INT DEFAULT NULL, DROP lieu_mediation');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D937133BD7 FOREIGN KEY (lieu_mediation_id) REFERENCES `member` (id)');
        $this->addSql('CREATE INDEX IDX_1FBFB8D937133BD7 ON prescription (lieu_mediation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D937133BD7');
        $this->addSql('DROP INDEX IDX_1FBFB8D937133BD7 ON prescription');
        $this->addSql('ALTER TABLE prescription ADD lieu_mediation VARCHAR(100) NOT NULL, DROP lieu_mediation_id');
    }
}
