<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240807221352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_verification DROP FOREIGN KEY FK_FE22358A76ED395');
        $this->addSql('ALTER TABLE password_reset DROP FOREIGN KEY FK_B1017252A76ED395');
        $this->addSql('DROP TABLE email_verification');
        $this->addSql('DROP TABLE password_reset');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE email_verification (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, auth_key VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_FE22358187D9ED4 (auth_key), INDEX IDX_FE22358A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE password_reset (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', auth_key VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_B1017252187D9ED4 (auth_key), INDEX IDX_B1017252A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE email_verification ADD CONSTRAINT FK_FE22358A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE password_reset ADD CONSTRAINT FK_B1017252A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }
}
