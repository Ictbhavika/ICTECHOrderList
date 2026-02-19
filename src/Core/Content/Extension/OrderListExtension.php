<?php

declare(strict_types=1);

namespace ICTECHOrderList\Core\Content\Extension;

use ICTECHOrderList\Core\Content\OrderList\OrderListDefination;
use ICTECHOrderList\Core\Content\OrderProductList\OrderProductListDefination;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OrderListExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField('orderListProduct', OrderProductListDefination::class, 'ictech_order_list_id', 'id')
        );
    }

    public function getEntityName(): string
    {
        return OrderListDefination::ENTITY_NAME;
    }
}
