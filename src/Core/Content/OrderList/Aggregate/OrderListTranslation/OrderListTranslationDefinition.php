<?php

declare(strict_types=1);

namespace ICTECHOrderList\Core\Content\OrderList\Aggregate\OrderListTranslation;

use ICTECHOrderList\Core\Content\OrderList\OrderListDefination;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OrderListTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'order_list_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return OrderListTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return OrderListTranslationEntity::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return OrderListDefination::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            new StringField('name', 'name'),
        ]);
    }
}
