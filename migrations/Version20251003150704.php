<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251003150704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, type_equipment VARCHAR(20) NOT NULL, brand_equipment VARCHAR(20) NOT NULL, matricul_equipment VARCHAR(100) NOT NULL, os_installed VARCHAR(20) NOT NULL, status_equipment VARCHAR(20) NOT NULL, is_dispo TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prescription (id INT AUTO_INCREMENT NOT NULL, membre_id INT DEFAULT NULL, beneficiaire_id INT DEFAULT NULL, equipement_id INT DEFAULT NULL, ref VARCHAR(100) NOT NULL, created_at DATE NOT NULL, updated_at DATE NOT NULL, INDEX IDX_1FBFB8D96A99F74A (membre_id), UNIQUE INDEX UNIQ_1FBFB8D95AF81F68 (beneficiaire_id), UNIQUE INDEX UNIQ_1FBFB8D9806F0F5C (equipement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D96A99F74A FOREIGN KEY (membre_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D95AF81F68 FOREIGN KEY (beneficiaire_id) REFERENCES beneficiary (id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D9806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipment (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D96A99F74A');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D95AF81F68');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D9806F0F5C');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE prescription');
    }
}
