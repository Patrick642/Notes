<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240728115529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE22358187D9ED4 ON email_verification (auth_key)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B1017252187D9ED4 ON password_reset (auth_key)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_FE22358187D9ED4 ON email_verification');
        $this->addSql('DROP INDEX UNIQ_B1017252187D9ED4 ON password_reset');
    }
}
