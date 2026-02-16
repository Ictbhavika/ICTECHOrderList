<?php

declare(strict_types=1);

namespace ICTECHOrderList\Core\Content\Extension;

use ICTECHOrderList\Core\Content\OrderList\Aggregate\OrderListTranslation\OrderListTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;

class LanguageExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'orderListTranslations',
                OrderListTranslationDefinition::class,
                'language_id'
            )
        );
    }

    public function getDefinitionClass(): string
    {
        return LanguageDefinition::class;
    }

    public function getEntityName(): string
    {
        return LanguageDefinition::ENTITY_NAME;
    }
}
