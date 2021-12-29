<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211213232239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_feedback ADD review_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_feedback ADD CONSTRAINT FK_19992ECFAEDDE433 FOREIGN KEY (review_order_id) REFERENCES `order` (id)');
        $this->addSql('CREATE INDEX IDX_19992ECFAEDDE433 ON product_feedback (review_order_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_feedback DROP FOREIGN KEY FK_19992ECFAEDDE433');
        $this->addSql('DROP INDEX IDX_19992ECFAEDDE433 ON product_feedback');
        $this->addSql('ALTER TABLE product_feedback DROP review_order_id');
    }
}
