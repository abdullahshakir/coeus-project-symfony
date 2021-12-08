<?php

namespace App\Entity;

use App\Repository\OrderProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=OrderProductRepository::class)
 */
class OrderProduct
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $order_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $product_id;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(1)
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="orderProducts")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="orderProducts")
     * @JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $productOrder;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status = self::STATUS_NEW;

    const STATUS_NEW = 'new';
    const STATUS_INPROGRESS = 'inprogress';
    const STATUS_COMPLETE = 'complete';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?int
    {
        return $this->order_id;
    }

    public function setOrderId(int $order_id): self
    {
        $this->order_id = $order_id;

        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    public function setProductId(int $product_id): self
    {
        $this->product_id = $product_id;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getProductOrder(): ?Order
    {
        return $this->productOrder;
    }

    public function setProductOrder(?Order $productOrder): self
    {
        $this->productOrder = $productOrder;

        return $this;
    }

    /**
     * Tests if the given product given corresponds to the same order product.
     *
     * @param OrderProduct $item
     *
     * @return bool
     */
    public function equals(OrderProduct $product): bool
    {
        return $this->getProduct()->getId() === $product->getProduct()->getId();
    }

    /**
     * Calculates the product total.
     *
     * @return float|int
     */
    public function getTotal(): float
    {
        return $this->getProduct()->getPrice() * $this->getQuantity();
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

}
