<?php

namespace App\Entity;

use App\Repository\ProductFeedbackRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\API\Review\Product\ProductReviewAddAction;

/**
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={
 *          "productId": "exact",
 *      }
 * )
 * @ApiResource(
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "controller"=ProductReviewAddAction::class
 *          }
 *      },
 *      itemOperations={
 *          "get"
 *      },
 *      normalizationContext={
 *          "groups"={"productfeedback:read"}
 *      },
 *      denormalizationContext={
 *          "groups"={"productfeedback:write"}
 *      }
 * )
 * @ORM\Entity(repositoryClass=ProductFeedbackRepository::class)
 */
class ProductFeedback
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"productfeedback:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"productfeedback:read", "productfeedback:write"})
     */
    private $message;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"productfeedback:read", "productfeedback:write"})
     */
    private $rating;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"productfeedback:read", "productfeedback:write"})
     */
    private $productId;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="productFeedback")
     * @Groups({"productfeedback:read"})
     */
    private $product;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="productFeedback")
     * @Groups({"productfeedback:read"})
     */
    private $reviewOrder;

    /**
     * @Groups({"productfeedback:write"})
     */
    private $reviewOrderId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getReviewOrder(): ?Order
    {
        return $this->reviewOrder;
    }

    public function setReviewOrder(?Order $reviewOrder): self
    {
        $this->reviewOrder = $reviewOrder;

        return $this;
    }

    public function setReviewOrderId(int $reviewOrderId): self
    {
        $this->reviewOrderId = $reviewOrderId;

        return $this;
    }

    public function getReviewOrderId(): ?int
    {
        return $this->reviewOrderId;
    }
}
