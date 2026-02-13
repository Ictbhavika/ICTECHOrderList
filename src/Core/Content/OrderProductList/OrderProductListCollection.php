<?php declare(strict_types=1);

namespace ICTECHOrderList\Core\Content\OrderProductList;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @package framework
 * @method void                add(OrderProductListEntity $entity)
 * @method void                set(string $key, OrderProductListEntity $entity)
 * @method OrderProductListEntity[]    getIterator()
 * @method OrderProductListEntity[]    getElements()
 * @method OrderProductListEntity|null get(string $key)
 * @method OrderProductListEntity|null first()
 * @method OrderProductListEntity|null last()
 */
class OrderProductListCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return OrderProductListEntity::class;
    }
}