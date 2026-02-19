<?php

declare(strict_types=1);

namespace ICTECHOrderList;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class ICTECHOrderList extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }
        $connection = $this->container?->get(Connection::class);
        if (!$connection instanceof Connection) {
            return;
        }
        $connection->executeStatement('DROP TABLE IF EXISTS `ictech_order_list_translation`');
        $connection->executeStatement('DROP TABLE IF EXISTS `ictech_order_product_list`');
        $connection->executeStatement('DROP TABLE IF EXISTS `ictech_order_list`');
        $connection->executeStatement(
            'DELETE FROM system_config WHERE configuration_key LIKE :domain',
            [
                'domain' => '%ICTECHOrderList.config%',
            ]
        );
    }
}
