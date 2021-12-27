<?php

namespace App\Controller\API\Review\Seller;

use App\Entity\UserFeedback;
use App\Entity\Order;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SellerReviewAddAction extends AbstractController
{
    private $entityManager;
    private $validator;
    private $orderRepository;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, OrderRepository $orderRepository, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->orderRepository = $orderRepository;
        $this->userRepository = $userRepository;
    }

    public function __invoke(UserFeedback $data)
    {
        $this->validator->validate($data);

        $reviewOrder = $this->orderRepository->find($data->getReviewOrderId());

        if (!$reviewOrder) {
            throw new \Exception('Invalid Review Order Id');
        }

        $orderSeller = $this->orderRepository->findBy([
            'userId' => $data->getUserId(),
            'id' => $data->getReviewOrderId()
        ]);

        if (!$orderSeller) {
            throw new \Exception('Invalid User Id');
        }

        $user = $this->userRepository->find($data->getUserId());

        $data->setReviewOrder($reviewOrder);
        $data->setUser($user);
        $data->setCreatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $data;
    }
}
