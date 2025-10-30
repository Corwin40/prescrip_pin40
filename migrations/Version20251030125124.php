<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251030125124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beneficiary DROP FOREIGN KEY FK_7ABF446A4923D850');
        $this->addSql('DROP INDEX UNIQ_7ABF446A4923D850 ON beneficiary');
        $this->addSql('ALTER TABLE beneficiary DROP beneficiary_competences_id');
        $this->addSql('ALTER TABLE competence CHANGE is_auto_eval_end is_auto_eva_end TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE prescription ADD competence_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D915761DAB FOREIGN KEY (competence_id) REFERENCES competence (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FBFB8D915761DAB ON prescription (competence_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE competence CHANGE is_auto_eva_end is_auto_eval_end TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE beneficiary ADD beneficiary_competences_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE beneficiary ADD CONSTRAINT FK_7ABF446A4923D850 FOREIGN KEY (beneficiary_competences_id) REFERENCES competence (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7ABF446A4923D850 ON beneficiary (beneficiary_competences_id)');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D915761DAB');
        $this->addSql('DROP INDEX UNIQ_1FBFB8D915761DAB ON prescription');
        $this->addSql('ALTER TABLE prescription DROP competence_id');
    }
}
