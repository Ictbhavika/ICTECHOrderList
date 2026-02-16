<?php

declare(strict_types=1);

namespace ICTECHOrderList\Core\Content\OrderList\Aggregate\OrderListTranslation;

use ICTECHOrderList\Core\Content\OrderList\OrderListEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class OrderListTranslationEntity extends TranslationEntity
{
    protected ?string $name = null;
    protected string $orderListId;
    protected ?OrderListEntity $orderList = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getOrderListId(): string
    {
        return $this->orderListId;
    }

    public function setOrderListId(string $orderListId): void
    {
        $this->orderListId = $orderListId;
    }

    public function getOrderList(): ?OrderListEntity
    {
        return $this->orderList;
    }

    public function setOrderList(?OrderListEntity $orderList): void
    {
        $this->orderList = $orderList;
    }
}
