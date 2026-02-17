<?php declare(strict_types=1);
 
namespace ICTECHOrderList\Core\Content\OrderProductList;
 
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Content\Product\ProductEntity;
use ICTECHOrderList\Core\Content\OrderList\OrderListEntity;
 
class OrderProductListEntity extends Entity
{
    use EntityIdTrait;
 
    /**
     * @var string
     */
    protected string $id;
 
    /**
     * @var string|null
     */
    protected $productNumber;
 
    /**
     * @var int|null
     */
    protected $qty;
 
    /**
     * @var string
     */
    protected $productId;
 
    /**
     * @var string
     */
    protected $orderListId;
 
    /**
     * @var ProductEntity|null
     */
    protected $product;
 
    /**
     * @var OrderListEntity|null
     */
    protected $orderList;
 
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
 
    public function getProductNumber(): ?string
    {
        return $this->productNumber;
    }
 
    public function setProductNumber(?string $productNumber): void
    {
        $this->productNumber = $productNumber;
    }
 
    public function getQty(): ?int
    {
        return $this->qty;
    }
 
    public function setQty(?int $qty): void
    {
        $this->qty = $qty;
    }
 
    public function getProductId(): string
    {
        return $this->productId;
    }
 
    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }
 
    public function getOrderListId(): string
    {
        return $this->orderListId;
    }
 
    public function setOrderListId(string $orderListId): void
    {
        $this->orderListId = $orderListId;
    }
 
    public function getProduct(): ProductEntity
    {
        return $this->product;
    }
 
    public function setProduct(ProductEntity $product): void
    {
        $this->product = $product;
    }
 
    public function getOrderList(): ?OrderListEntity
    {
        return $this->orderList;
    }
 
    public function setOrderList(?OrderListEntity $orderList): void
    {
        $this->orderList = $orderList;
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