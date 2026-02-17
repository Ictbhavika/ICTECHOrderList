<?php declare(strict_types=1);

namespace ICTECHOrderList\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('core')]
class Migration1771223708OrderProductList extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1771223708;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS `ictech_order_product_list` (
            `id` BINARY(16) NOT NULL,
            `product_number` VARCHAR(255) NULL,
            `qty` INT NULL,
            `product_id` BINARY(16) NOT NULL,
            `ictech_order_list_id` BINARY(16) NOT NULL,
            `product_version_id` BINARY(16) NOT NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`),
            CONSTRAINT fk_ictech_order_product_list_product_id
                FOREIGN KEY (`product_id`, `product_version_id`)
                REFERENCES `product` (`id`, `version_id`)
                ON DELETE CASCADE
                ON UPDATE CASCADE,
            CONSTRAINT fk_ictech_order_product_list_order_list_id
                FOREIGN KEY (`ictech_order_list_id`)
                REFERENCES `ictech_order_list` (`id`)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        $connection->executeStatement('DROP TABLE IF EXISTS `order_product_list`');
    }
}
