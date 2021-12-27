<?php

namespace App\Entity;

use App\Repository\UserFeedbackRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\API\Review\Seller\SellerReviewAddAction;
use App\Controller\API\Review\Seller\SellerReviewTopRatedAction;

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
 *              "controller"=SellerReviewAddAction::class
 *          },
 *          "gettoprated"={
 *              "method"="GET",
 *              "path"="/user_feedbacks/toprated",
 *              "controller"=SellerReviewTopRatedAction::class
 *          }
 *      },
 *      itemOperations={
 *          "get"
 *      },
 *      normalizationContext={
 *          "groups"={"sellerfeedback:read"}
 *      },
 *      denormalizationContext={
 *          "groups"={"sellerfeedback:write"}
 *      }
 * )
 * @ORM\Entity(repositoryClass=UserFeedbackRepository::class)
 */
class UserFeedback
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"sellerfeedback:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"sellerfeedback:read", "sellerfeedback:write"})
     */
    private $message;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"sellerfeedback:write"})
     */
    private $userId;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"sellerfeedback:read", "sellerfeedback:write"})
     */
    private $rating;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userFeedback")
     * @Groups({"sellerfeedback:read"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="userFeedback")
     */
    private $reviewOrder;

    /**
     * @Groups({"sellerfeedback:write"})
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

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

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
