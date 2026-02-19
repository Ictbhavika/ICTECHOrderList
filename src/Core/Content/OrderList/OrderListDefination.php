<?php

declare(strict_types=1);

namespace ICTECHOrderList\Core\Content\OrderList;

use ICTECHOrderList\Core\Content\OrderList\Aggregate\OrderListTranslation\OrderListTranslationDefinition;
use ICTECHOrderList\Core\Content\OrderProductList\OrderProductListDefination;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OrderListDefination extends EntityDefinition
{
    public const ENTITY_NAME = 'ictech_order_list';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }
    public function getCollectionClass(): string
    {
        return OrderListCollection::class;
    }

    public function getEntityClass(): string
    {
        return OrderListEntity::class;
    }
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('customer_id', 'customerId', CustomerDefinition::class))->addFlags(new Required(), new ApiAware()),
            (new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class, 'id', false))->addFlags(new ApiAware()),
            new TranslatedField('name'),
            (new TranslationsAssociationField(OrderListTranslationDefinition::class, 'ictech_order_list_id'))->addFlags(new ApiAware(), new Required()),
            (new OneToManyAssociationField('products', OrderProductListDefination::class, 'ictech_order_list_id'))->addFlags(new ApiAware()),
            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
