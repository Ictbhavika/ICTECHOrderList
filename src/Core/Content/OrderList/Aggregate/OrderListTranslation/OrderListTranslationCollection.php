<?php

declare(strict_types=1);

namespace ICTECHOrderList\Core\Content\OrderList\Aggregate\OrderListTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                              add(OrderListTranslationEntity $entity)
 * @method void                              set(string $key, OrderListTranslationEntity $entity)
 * @method OrderListTranslationEntity[]      getIterator()
 * @method OrderListTranslationEntity[]      getElements()
 * @method OrderListTranslationEntity|null   get(string $key)
 * @method OrderListTranslationEntity|null   first()
 * @method OrderListTranslationEntity|null   last()
 */
class OrderListTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return OrderListTranslationEntity::class;
    }
}
