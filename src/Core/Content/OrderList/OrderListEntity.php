<?php declare(strict_types=1);

namespace ICTECHOrderList\Core\Content\OrderList;

use ICTECHOrderList\Core\Content\OrderList\Aggregate\OrderListTranslation\OrderListTranslationCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use ICTECHOrderList\Core\Content\OrderProductList\OrderProductListCollection;

class OrderListEntity extends Entity
{
    use EntityIdTrait;

    protected string $id;
    protected ?string $name = null;
    protected string $customerId;
    protected ?CustomerEntity $customer = null;
    protected ?OrderListTranslationCollection $translations = null;
    protected ?OrderProductListCollection $products = null;
    protected ?\DateTimeInterface $createdAt = null;
    protected ?\DateTimeInterface $updatedAt = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    public function getTranslations(): ?OrderListTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(OrderListTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getProducts(): ?OrderProductListCollection
    {
        return $this->products;
    }

    public function setProducts(OrderProductListCollection $products): void
    {
        $this->products = $products;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}