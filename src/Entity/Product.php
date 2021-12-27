<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\API\Product\ProductAddAction;
use App\Controller\API\Product\ProductEditAction;
use App\Controller\API\Product\ProductMostPopularAction;

/**
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={
 *          "userId": "exact",
 *      }
 * )
 * @ApiResource(
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "controller"=ProductAddAction::class
 *          },
 *          "getmostpopular"={
 *              "method"="GET",
 *              "path"="/most-popular-products",
 *              "controller"=ProductMostPopularAction::class
 *          }
 *      },
 *      itemOperations={
 *          "get",
 *          "put"={
 *              "controller"=ProductEditAction::class
 *          },
 *          "delete"
 *      },
 *      normalizationContext={
 *          "groups"={"product:read"}
 *      },
 *      denormalizationContext={
 *          "groups"={"product:write"}
 *      }
 * )
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"product:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"product:read", "product:write"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"product:read", "product:write"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"product:read", "product:write"})
     */
    private $imageLink;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read", "product:write"})
     */
    private $categoryId;

    /**
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="products")
     * @Groups({"product:read"})
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="products")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=OrderProduct::class, mappedBy="product")
     */
    private $orderProducts;

    /**
     * @ORM\Column(type="float")
     * @Groups({"product:read", "product:write"})
     */
    private $price;

    /**
     * @ORM\Column(type="bigint")
     * @Groups({"product:read", "product:write"})
     */
    private $quantity;

    /**
     * @ORM\OneToMany(targetEntity=ProductFeedback::class, mappedBy="product")
     */
    private $productFeedback;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"product:read", "product:write"})
     */
    private $status = self::STATUS_ACTIVE;

    const STATUS_ACTIVE = 'active';
    const STATUS_DISABLED = 'disabled';

    public function __construct()
    {
        $this->orderProducts = new ArrayCollection();
        $this->productFeedback = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImageLink(): ?string
    {
        return $this->imageLink;
    }

    public function setImageLink(string $imageLink): self
    {
        $this->imageLink = $imageLink;

        return $this;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): self
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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

    /**
     * @return Collection|OrderProduct[]
     */
    public function getOrderProducts(): Collection
    {
        return $this->orderProducts;
    }

    public function addOrderProduct(OrderProduct $orderProduct): self
    {
        if (!$this->orderProducts->contains($orderProduct)) {
            $this->orderProducts[] = $orderProduct;
            $orderProduct->setProduct($this);
        }

        return $this;
    }

    public function removeOrderProduct(OrderProduct $orderProduct): self
    {
        if ($this->orderProducts->removeElement($orderProduct)) {
            // set the owning side to null (unless already changed)
            if ($orderProduct->getProduct() === $this) {
                $orderProduct->setProduct(null);
            }
        }

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): self
    {
        $this->quantity = $quantity;

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
            $productFeedback->setProduct($this);
        }

        return $this;
    }

    public function removeProductFeedback(ProductFeedback $productFeedback): self
    {
        if ($this->productFeedback->removeElement($productFeedback)) {
            // set the owning side to null (unless already changed)
            if ($productFeedback->getProduct() === $this) {
                $productFeedback->setProduct(null);
            }
        }

        return $this;
    }

    public function getAverageRating()
    {
        $count = $this->getProductFeedback()->count();

        if ($count == 0) {
            return 0;
        }

        $feedbacks = $this->getProductFeedback();
        $totalRating = null;
        
        foreach ($feedbacks as $feedback) {
            $totalRating += $feedback->getRating();
        }

        return round($totalRating/$count, 1);
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
