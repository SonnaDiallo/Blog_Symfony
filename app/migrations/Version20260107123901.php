<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260107123901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25274584665A');
        $this->addSql('DROP INDEX IDX_F0FE25274584665A ON cart_item');
        $this->addSql('ALTER TABLE cart_item CHANGE product_id post_id INT NOT NULL');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25274B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('CREATE INDEX IDX_F0FE25274B89032C ON cart_item (post_id)');
        $this->addSql('ALTER TABLE post ADD price NUMERIC(10, 2) DEFAULT NULL, ADD stock INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP price, DROP stock');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25274B89032C');
        $this->addSql('DROP INDEX IDX_F0FE25274B89032C ON cart_item');
        $this->addSql('ALTER TABLE cart_item CHANGE post_id product_id INT NOT NULL');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25274584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F0FE25274584665A ON cart_item (product_id)');
    }
}
