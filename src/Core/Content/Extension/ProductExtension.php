<?php

declare(strict_types=1);

namespace ICTECHOrderList\Core\Content\Extension;

use ICTECHOrderList\Core\Content\OrderProductList\OrderProductListDefination;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField('orderListProduct', OrderProductListDefination::class, 'order_list_product_id', 'id')
        );
    }
  
    public function getEntityName(): string
    {
        return ProductDefinition::ENTITY_NAME;
    }
}
