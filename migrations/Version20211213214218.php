<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211213214218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_feedback ADD review_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_feedback ADD CONSTRAINT FK_32A4A058AEDDE433 FOREIGN KEY (review_order_id) REFERENCES `order` (id)');
        $this->addSql('CREATE INDEX IDX_32A4A058AEDDE433 ON user_feedback (review_order_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_feedback DROP FOREIGN KEY FK_32A4A058AEDDE433');
        $this->addSql('DROP INDEX IDX_32A4A058AEDDE433 ON user_feedback');
        $this->addSql('ALTER TABLE user_feedback DROP review_order_id');
    }
}
