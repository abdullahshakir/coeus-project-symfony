<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $user_id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status = self::STATUS_CART;

    /**
     * @ORM\OneToMany(targetEntity=OrderProduct::class, mappedBy="productOrder", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $orderProducts;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\OneToMany(targetEntity=UserFeedback::class, mappedBy="reviewOrder")
     */
    private $userFeedback;

    /**
     * @ORM\OneToMany(targetEntity=ProductFeedback::class, mappedBy="reviewOrder")
     */
    private $productFeedback;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isNotified = false;

    const STATUS_CART = 'cart';
    const STATUS_NEW = 'new';
    const STATUS_DELIVERED = 'delivered';

    public function __construct()
    {
        $this->orderProducts = new ArrayCollection();
        $this->userFeedback = new ArrayCollection();
        $this->productFeedback = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
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

    /**
     * @return Collection|OrderProduct[]
     */
    public function getOrderProducts(): Collection
    {
        return $this->orderProducts;
    }

    public function addOrderProduct(OrderProduct $orderProduct): self
    {
        foreach ($this->getOrderProducts() as $existingProduct) {
            // The product already exists, update the quantity
            if ($existingProduct->equals($orderProduct)) {
                $existingProduct->setQuantity(
                    $existingProduct->getQuantity() + $orderProduct->getQuantity()
                );
                return $this;
            }
        }
    
        $this->orderProducts[] = $orderProduct;
        $orderProduct->setProductOrder($this);

        return $this;
    }

    public function removeOrderProduct(OrderProduct $orderProduct): self
    {
        if ($this->orderProducts->removeElement($orderProduct)) {
            // set the owning side to null (unless already changed)
            if ($orderProduct->getProductOrder() === $this) {
                $orderProduct->setProductOrder(null);
            }
        }

        return $this;
    }

    /**
     * Removes all items from the order.
     *
     * @return $this
     */
    public function removeOrderProducts(): self
    {
        foreach ($this->getOrderProducts() as $product) {
            $this->removeOrderProduct($product);
        }

        return $this;
    }

    /**
     * Calculates the order total.
     *
     * @return float
     */
    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getOrderProducts() as $product) {
            $total += $product->getTotal();
        }

        return $total;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getReviewedUserIds()
    {
        $reviewedUsers = $this->getUserFeedback();
        $reviewedUserIds = [];

        foreach ($reviewedUsers as $reviewedUser) {
            $reviewedUserIds[] = $reviewedUser->getUserId();
        } 
        
        return $reviewedUserIds;
    }

    public function getReviewedProductIds()
    {
        $reviewedProducts = $this->getProductFeedback();
        $reviewedProductIds = [];

        foreach ($reviewedProducts as $reviewedProduct) {
            $reviewedProductIds[] = $reviewedProduct->getProductId();
        } 
        
        return $reviewedProductIds;
    }

    public function getSellers()
    {
        $orderProducts = $this->getOrderProducts();
        $sellerIds = [];

        foreach ($orderProducts as $orderProduct) {
            if (!in_array($orderProduct->getProduct()->getUserId(), $sellerIds)) {
                $sellerIds[] = $orderProduct->getProduct()->getUserId();
            }
        } 
        
        return $sellerIds;
    }

    public function getOrderProductsIds()
    {
        $orderProducts = $this->getOrderProducts();
        $orderProductsIds = [];

        foreach ($orderProducts as $orderProduct) {
            $orderProductsIds[] = $orderProduct->getProductId();
        } 
        
        return $orderProductsIds;
    }

    /**
     * @return Collection|UserFeedback[]
     */
    public function getUserFeedback(): Collection
    {
        return $this->userFeedback;
    }

    public function addUserFeedback(UserFeedback $userFeedback): self
    {
        if (!$this->userFeedback->contains($userFeedback)) {
            $this->userFeedback[] = $userFeedback;
            $userFeedback->setReviewOrder($this);
        }

        return $this;
    }

    public function removeUserFeedback(UserFeedback $userFeedback): self
    {
        if ($this->userFeedback->removeElement($userFeedback)) {
            // set the owning side to null (unless already changed)
            if ($userFeedback->getReviewOrder() === $this) {
                $userFeedback->setReviewOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductFeedback[]
     */
    public function getProductFeedback(): Collection
    {
        return $this->productFeedback;
    }

    public function addProductFeedback(ProductFeedback $productFeedback): self
    {
        if (!$this->productFeedback->contains($productFeedback)) {
            $this->productFeedback[] = $productFeedback;
            $productFeedback->setReviewOrder($this);
        }

        return $this;
    }

    public function removeProductFeedback(ProductFeedback $productFeedback): self
    {
        if ($this->productFeedback->removeElement($productFeedback)) {
            // set the owning side to null (unless already changed)
            if ($productFeedback->getReviewOrder() === $this) {
                $productFeedback->setReviewOrder(null);
            }
        }

        return $this;
    }

    public function getIsNotified(): ?bool
    {
        return $this->isNotified;
    }

    public function setIsNotified(bool $isNotified): self
    {
        $this->isNotified = $isNotified;

        return $this;
    }

}
