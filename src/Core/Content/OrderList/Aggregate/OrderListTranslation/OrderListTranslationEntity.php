<?php

declare(strict_types=1);

namespace ICTECHOrderList\Core\Content\OrderList\Aggregate\OrderListTranslation;

use ICTECHOrderList\Core\Content\OrderList\OrderListEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class OrderListTranslationEntity extends TranslationEntity
{
    protected ?string $name = null;
    protected string $ictechOrderListId;
    protected ?OrderListEntity $ictechOrderList = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getIctechOrderListId(): string
    {
        return $this->ictechOrderListId;
    }

    public function setIctechOrderListId(string $ictechOrderListId): void
    {
        $this->ictechOrderListId = $ictechOrderListId;
    }

    public function getIctechOrderList(): ?OrderListEntity
    {
        return $this->ictechOrderList;
    }

    public function setIctechOrderList(?OrderListEntity $ictechOrderList): void
    {
        $this->ictechOrderList = $ictechOrderList;
    }
}
