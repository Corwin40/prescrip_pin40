<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251020141846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE beneficiary (id INT AUTO_INCREMENT NOT NULL, beneficiary_competences_id INT DEFAULT NULL, firstname VARCHAR(100) NOT NULL, lastname VARCHAR(100) NOT NULL, civility VARCHAR(6) NOT NULL, gender VARCHAR(10) NOT NULL, age_group VARCHAR(25) DEFAULT NULL, professionnal_status VARCHAR(40) NOT NULL, created_at DATE NOT NULL, updated_at DATE NOT NULL, UNIQUE INDEX UNIQ_7ABF446A4923D850 (beneficiary_competences_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE competence (id INT AUTO_INCREMENT NOT NULL, comp_base VARCHAR(100) NOT NULL, comp_desk VARCHAR(100) NOT NULL, comp_internet VARCHAR(100) NOT NULL, comp_email VARCHAR(100) NOT NULL, is_auto_eva TINYINT(1) NOT NULL, is_dig_comp0 TINYINT(1) NOT NULL, is_dig_comp1 TINYINT(1) NOT NULL, is_dig_comp2 TINYINT(1) NOT NULL, is_dig_comp3 TINYINT(1) NOT NULL, is_dig_comp4 TINYINT(1) NOT NULL, is_dig_comp5 TINYINT(1) NOT NULL, detail_parcour LONGTEXT NOT NULL, is_auto_eval_end TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, type_equipment VARCHAR(20) NOT NULL, brand_equipment VARCHAR(20) NOT NULL, matricul_equipment VARCHAR(100) NOT NULL, os_installed VARCHAR(20) NOT NULL, status_equipment VARCHAR(20) NOT NULL, is_dispo TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `member` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name_structure VARCHAR(150) NOT NULL, address VARCHAR(255) DEFAULT NULL, zipcode VARCHAR(5) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, contact_email VARCHAR(255) DEFAULT NULL, contact_phone VARCHAR(14) DEFAULT NULL, contact_responsable_firstname VARCHAR(100) DEFAULT NULL, contact_responsable_lastname VARCHAR(100) DEFAULT NULL, contact_responsable_civility VARCHAR(4) DEFAULT NULL, is_verified TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prescription (id INT AUTO_INCREMENT NOT NULL, membre_id INT DEFAULT NULL, beneficiaire_id INT DEFAULT NULL, equipement_id INT DEFAULT NULL, ref VARCHAR(100) NOT NULL, created_at DATE NOT NULL, updated_at DATE NOT NULL, details LONGTEXT DEFAULT NULL, base_competence VARCHAR(100) NOT NULL, lieu_mediation VARCHAR(100) NOT NULL, INDEX IDX_1FBFB8D96A99F74A (membre_id), UNIQUE INDEX UNIQ_1FBFB8D95AF81F68 (beneficiaire_id), UNIQUE INDEX UNIQ_1FBFB8D9806F0F5C (equipement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE beneficiary ADD CONSTRAINT FK_7ABF446A4923D850 FOREIGN KEY (beneficiary_competences_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D96A99F74A FOREIGN KEY (membre_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D95AF81F68 FOREIGN KEY (beneficiaire_id) REFERENCES beneficiary (id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D9806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipment (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beneficiary DROP FOREIGN KEY FK_7ABF446A4923D850');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D96A99F74A');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D95AF81F68');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D9806F0F5C');
        $this->addSql('DROP TABLE beneficiary');
        $this->addSql('DROP TABLE competence');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE `member`');
        $this->addSql('DROP TABLE prescription');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
