<?php

namespace App\Controller\API\Review\Product;

use App\Entity\ProductFeedback;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\OrderProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductReviewAddAction extends AbstractController
{
    private $entityManager;
    private $validator;
    private $orderRepository;
    private $orderProductRepository;
    private $productRepository;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, OrderRepository $orderRepository, OrderProductRepository $orderProductRepository, ProductRepository $productRepository)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->orderRepository = $orderRepository;
        $this->orderProductRepository = $orderProductRepository;
        $this->productRepository = $productRepository;
    }

    public function __invoke(ProductFeedback $data)
    {
        $this->validator->validate($data);

        $reviewOrder = $this->orderRepository->find($data->getReviewOrderId());

        if (!$reviewOrder) {
            throw new \Exception('Invalid Review Order Id');
        }

        $orderProduct = $this->orderProductRepository->findBy([
            'order_id' => $data->getReviewOrderId(),
            'productId' => $data->getProductId()
        ]);

        if (!$orderProduct) {
            throw new \Exception('Invalid Product Id');
        }

        $product = $this->productRepository->find($data->getProductId());

        $data->setReviewOrder($reviewOrder);
        $data->setProduct($product);
        $data->setCreatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $data;
    }
}
