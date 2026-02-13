<?php declare(strict_types=1);

namespace ICTECHOrderList\Core\Content\OrderList;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use ICTECHOrderList\Core\Content\OrderProductList\OrderProductListCollection;

class OrderListEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected string $id;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string
     */
    protected $customerId;

    /**
     * @var CustomerEntity|null
     */
    protected $customer;

    /**
     * @var \DateTimeInterface
     */
    protected ?\DateTimeInterface $createdAt;

    /**
     * @var \DateTimeInterface|null
     */
    protected ?\DateTimeInterface $updatedAt;

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