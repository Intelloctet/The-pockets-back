<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231220061204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE profil_id_seq CASCADE');
        $this->addSql('ALTER TABLE profil ALTER id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN profil.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE users ADD profil_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD is_blocked BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE users ADD is_deleted BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE users ADD type_of_password VARCHAR(10) NOT NULL');
        $this->addSql('COMMENT ON COLUMN users.profil_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9275ED078 FOREIGN KEY (profil_id) REFERENCES profil (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9275ED078 ON users (profil_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE profil_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9275ED078');
        $this->addSql('DROP INDEX UNIQ_1483A5E9275ED078');
        $this->addSql('ALTER TABLE users DROP profil_id');
        $this->addSql('ALTER TABLE users DROP is_blocked');
        $this->addSql('ALTER TABLE users DROP is_deleted');
        $this->addSql('ALTER TABLE users DROP type_of_password');
        $this->addSql('ALTER TABLE profil ALTER id TYPE INT');
        $this->addSql('COMMENT ON COLUMN profil.id IS NULL');
    }
}
