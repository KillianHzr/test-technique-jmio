<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260510141014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE selection (id INT AUTO_INCREMENT NOT NULL, recruiter_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_96A50CD7156BE243 (recruiter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE selection_freelance_conso (selection_id INT NOT NULL, freelance_conso_id INT NOT NULL, INDEX IDX_9C8DB440E48EFE78 (selection_id), INDEX IDX_9C8DB440BEEBC9A0 (freelance_conso_id), PRIMARY KEY(selection_id, freelance_conso_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, company_name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE selection ADD CONSTRAINT FK_96A50CD7156BE243 FOREIGN KEY (recruiter_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE selection_freelance_conso ADD CONSTRAINT FK_9C8DB440E48EFE78 FOREIGN KEY (selection_id) REFERENCES selection (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE selection_freelance_conso ADD CONSTRAINT FK_9C8DB440BEEBC9A0 FOREIGN KEY (freelance_conso_id) REFERENCES freelance_conso (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE freelance_conso ADD skills JSON DEFAULT NULL, ADD bio LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE freelance_jean_paul ADD skills JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE freelance_linked_in ADD skills JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE selection DROP FOREIGN KEY FK_96A50CD7156BE243');
        $this->addSql('ALTER TABLE selection_freelance_conso DROP FOREIGN KEY FK_9C8DB440E48EFE78');
        $this->addSql('ALTER TABLE selection_freelance_conso DROP FOREIGN KEY FK_9C8DB440BEEBC9A0');
        $this->addSql('DROP TABLE selection');
        $this->addSql('DROP TABLE selection_freelance_conso');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('ALTER TABLE freelance_linked_in DROP skills');
        $this->addSql('ALTER TABLE freelance_conso DROP skills, DROP bio');
        $this->addSql('ALTER TABLE freelance_jean_paul DROP skills');
    }
}
