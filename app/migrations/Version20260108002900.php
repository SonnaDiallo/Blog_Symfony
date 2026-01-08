<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260108002900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create post_like table for likes system';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE post_like (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            post_id INT NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_653627B8A76ED395 (user_id),
            INDEX IDX_653627B84B89032C (post_id),
            UNIQUE INDEX unique_user_post_like (user_id, post_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('ALTER TABLE post_like ADD CONSTRAINT FK_653627B8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post_like ADD CONSTRAINT FK_653627B84B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post_like DROP FOREIGN KEY FK_653627B8A76ED395');
        $this->addSql('ALTER TABLE post_like DROP FOREIGN KEY FK_653627B84B89032C');
        $this->addSql('DROP TABLE post_like');
    }
}
