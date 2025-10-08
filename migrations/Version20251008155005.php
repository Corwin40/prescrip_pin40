<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251008155005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE competence (id INT AUTO_INCREMENT NOT NULL, comp_base VARCHAR(100) NOT NULL, comp_desk VARCHAR(100) NOT NULL, comp_internet VARCHAR(100) NOT NULL, comp_email VARCHAR(100) NOT NULL, is_auto_eva TINYINT(1) NOT NULL, is_dig_comp1 TINYINT(1) NOT NULL, is_dig_comp2 TINYINT(1) NOT NULL, is_dig_comp3 TINYINT(1) NOT NULL, is_dig_comp4 TINYINT(1) NOT NULL, is_dig_comp5 TINYINT(1) NOT NULL, detail_parcour LONGTEXT NOT NULL, is_auto_eval_end TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE beneficiary DROP FOREIGN KEY FK_7ABF446A93DB413D');
        $this->addSql('DROP INDEX UNIQ_7ABF446A93DB413D ON beneficiary');
        $this->addSql('ALTER TABLE beneficiary ADD beneficiary_competences_id INT DEFAULT NULL, DROP prescription_id');
        $this->addSql('ALTER TABLE beneficiary ADD CONSTRAINT FK_7ABF446A4923D850 FOREIGN KEY (beneficiary_competences_id) REFERENCES competence (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7ABF446A4923D850 ON beneficiary (beneficiary_competences_id)');
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D58393DB413D');
        $this->addSql('DROP INDEX UNIQ_D338D58393DB413D ON equipment');
        $this->addSql('ALTER TABLE equipment DROP prescription_id, CHANGE matricul_equipment matricul_equipment VARCHAR(100) NOT NULL, CHANGE computer_brand brand_equipment VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D972BE9C8C');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D9C9E3CC83');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D9F5A26FD9');
        $this->addSql('DROP INDEX UNIQ_1FBFB8D972BE9C8C ON prescription');
        $this->addSql('DROP INDEX UNIQ_1FBFB8D9C9E3CC83 ON prescription');
        $this->addSql('DROP INDEX IDX_1FBFB8D9F5A26FD9 ON prescription');
        $this->addSql('ALTER TABLE prescription ADD membre_id INT DEFAULT NULL, ADD beneficiaire_id INT DEFAULT NULL, ADD equipement_id INT DEFAULT NULL, ADD détails LONGTEXT NOT NULL, ADD base_competence VARCHAR(100) NOT NULL, ADD lieu_mediation VARCHAR(100) NOT NULL, DROP id_member_id, DROP id_benefiaciary_id, DROP id_equipment_id, CHANGE ref ref VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D96A99F74A FOREIGN KEY (membre_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D95AF81F68 FOREIGN KEY (beneficiaire_id) REFERENCES beneficiary (id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D9806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipment (id)');
        $this->addSql('CREATE INDEX IDX_1FBFB8D96A99F74A ON prescription (membre_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FBFB8D95AF81F68 ON prescription (beneficiaire_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FBFB8D9806F0F5C ON prescription (equipement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beneficiary DROP FOREIGN KEY FK_7ABF446A4923D850');
        $this->addSql('DROP TABLE competence');
        $this->addSql('DROP INDEX UNIQ_7ABF446A4923D850 ON beneficiary');
        $this->addSql('ALTER TABLE beneficiary ADD prescription_id INT NOT NULL, DROP beneficiary_competences_id');
        $this->addSql('ALTER TABLE beneficiary ADD CONSTRAINT FK_7ABF446A93DB413D FOREIGN KEY (prescription_id) REFERENCES prescription (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7ABF446A93DB413D ON beneficiary (prescription_id)');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D96A99F74A');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D95AF81F68');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D9806F0F5C');
        $this->addSql('DROP INDEX IDX_1FBFB8D96A99F74A ON prescription');
        $this->addSql('DROP INDEX UNIQ_1FBFB8D95AF81F68 ON prescription');
        $this->addSql('DROP INDEX UNIQ_1FBFB8D9806F0F5C ON prescription');
        $this->addSql('ALTER TABLE prescription ADD id_member_id INT DEFAULT NULL, ADD id_benefiaciary_id INT DEFAULT NULL, ADD id_equipment_id INT NOT NULL, DROP membre_id, DROP beneficiaire_id, DROP equipement_id, DROP détails, DROP base_competence, DROP lieu_mediation, CHANGE ref ref VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D972BE9C8C FOREIGN KEY (id_equipment_id) REFERENCES equipment (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D9C9E3CC83 FOREIGN KEY (id_benefiaciary_id) REFERENCES beneficiary (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D9F5A26FD9 FOREIGN KEY (id_member_id) REFERENCES `member` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FBFB8D972BE9C8C ON prescription (id_equipment_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FBFB8D9C9E3CC83 ON prescription (id_benefiaciary_id)');
        $this->addSql('CREATE INDEX IDX_1FBFB8D9F5A26FD9 ON prescription (id_member_id)');
        $this->addSql('ALTER TABLE equipment ADD prescription_id INT NOT NULL, CHANGE matricul_equipment matricul_equipment VARCHAR(100) DEFAULT NULL, CHANGE brand_equipment computer_brand VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D58393DB413D FOREIGN KEY (prescription_id) REFERENCES prescription (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D338D58393DB413D ON equipment (prescription_id)');
    }
}
