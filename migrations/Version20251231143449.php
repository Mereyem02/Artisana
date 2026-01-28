<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251231143449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artisan DROP INDEX FK_3C600AD3A76ED395, ADD UNIQUE INDEX UNIQ_3C600AD3A76ED395 (user_id)');
        $this->addSql('ALTER TABLE artisan CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE artisan ADD CONSTRAINT FK_3C600AD3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE artisan ADD CONSTRAINT FK_3C600AD38D0C5D40 FOREIGN KEY (cooperative_id) REFERENCES cooperative (id)');
        $this->addSql('ALTER TABLE artisan_product ADD CONSTRAINT FK_E7C591BC5ED3C7B7 FOREIGN KEY (artisan_id) REFERENCES artisan (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artisan_product ADD CONSTRAINT FK_E7C591BC4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE order_item ADD order_id INT NOT NULL, ADD product_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_52EA1F098D9F6D38 ON order_item (order_id)');
        $this->addSql('CREATE INDEX IDX_52EA1F094584665A ON order_item (product_id)');
        $this->addSql('ALTER TABLE product DROP sku');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD8D0C5D40 FOREIGN KEY (cooperative_id) REFERENCES cooperative (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD3BDE5358 FOREIGN KEY (a_id) REFERENCES product_media (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C64584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artisan DROP INDEX UNIQ_3C600AD3A76ED395, ADD INDEX FK_3C600AD3A76ED395 (user_id)');
        $this->addSql('ALTER TABLE artisan DROP FOREIGN KEY FK_3C600AD3A76ED395');
        $this->addSql('ALTER TABLE artisan DROP FOREIGN KEY FK_3C600AD38D0C5D40');
        $this->addSql('ALTER TABLE artisan CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE artisan_product DROP FOREIGN KEY FK_E7C591BC5ED3C7B7');
        $this->addSql('ALTER TABLE artisan_product DROP FOREIGN KEY FK_E7C591BC4584665A');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F098D9F6D38');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F094584665A');
        $this->addSql('DROP INDEX IDX_52EA1F098D9F6D38 ON order_item');
        $this->addSql('DROP INDEX IDX_52EA1F094584665A ON order_item');
        $this->addSql('ALTER TABLE order_item DROP order_id, DROP product_id');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD8D0C5D40');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD3BDE5358');
        $this->addSql('ALTER TABLE product ADD sku VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C64584665A');
    }
}
