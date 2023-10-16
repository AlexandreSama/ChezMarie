<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231016125140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reference ADD invoice_id INT NOT NULL');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA349132989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('CREATE INDEX IDX_AEA349132989F1FD ON reference (invoice_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA349132989F1FD');
        $this->addSql('DROP INDEX IDX_AEA349132989F1FD ON reference');
        $this->addSql('ALTER TABLE reference DROP invoice_id');
    }
}
