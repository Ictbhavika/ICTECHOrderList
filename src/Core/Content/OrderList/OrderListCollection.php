<?php declare(strict_types=1);

namespace ICTECHOrderList\Core\Content\OrderList;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @package framework
 * @method void                add(OrderListEntity $entity)
 * @method void                set(string $key, OrderListEntity $entity)
 * @method OrderListEntity[]    getIterator()
 * @method OrderListEntity[]    getElements()
 * @method OrderListEntity|null get(string $key)
 * @method OrderListEntity|null first()
 * @method OrderListEntity|null last()
 */
class OrderListCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return OrderListEntity::class;
    }
}