<?php

declare(strict_types=1);

namespace ICTECHOrderList\Core\Content\Extension;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use ICTECHOrderList\Core\Content\OrderList\OrderListDefination;
use Shopware\Core\Checkout\Customer\CustomerDefinition;

class CustomerExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField('orderList', OrderListDefination::class, 'order_list_id','id')
        );
    }
  
    public function getEntityName(): string
    {
        return CustomerDefinition::ENTITY_NAME;
    }

}
