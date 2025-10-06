<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006142605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE competence (id INT AUTO_INCREMENT NOT NULL, comp_base VARCHAR(100) NOT NULL, comp_desk VARCHAR(100) NOT NULL, comp_internet VARCHAR(100) NOT NULL, comp_email VARCHAR(100) NOT NULL, is_auto_eva TINYINT(1) NOT NULL, is_dig_comp1 TINYINT(1) NOT NULL, is_dig_comp2 TINYINT(1) NOT NULL, is_dig_comp3 TINYINT(1) NOT NULL, is_dig_comp4 TINYINT(1) NOT NULL, is_dig_comp5 TINYINT(1) NOT NULL, detail_parcour LONGTEXT NOT NULL, is_auto_eval_end TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE beneficiary ADD beneficiary_competences_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE beneficiary ADD CONSTRAINT FK_7ABF446A4923D850 FOREIGN KEY (beneficiary_competences_id) REFERENCES competence (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7ABF446A4923D850 ON beneficiary (beneficiary_competences_id)');
        $this->addSql('ALTER TABLE prescription ADD détails LONGTEXT NOT NULL, ADD base_competence VARCHAR(100) NOT NULL, ADD lieu_mediation VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beneficiary DROP FOREIGN KEY FK_7ABF446A4923D850');
        $this->addSql('DROP TABLE competence');
        $this->addSql('DROP INDEX UNIQ_7ABF446A4923D850 ON beneficiary');
        $this->addSql('ALTER TABLE beneficiary DROP beneficiary_competences_id');
        $this->addSql('ALTER TABLE prescription DROP détails, DROP base_competence, DROP lieu_mediation');
    }
}
