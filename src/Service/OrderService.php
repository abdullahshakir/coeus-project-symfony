<?php 

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductRepository;


class OrderService
{
    private $entityManager;
    private $productRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
    }

    public function editOrder($status, $order, $clonedOrder, $updatedOrderProducts)
    {
        if (isset($status)) {
            $order->setStatus($status);
            
            $orderProducts = $order->getOrderProducts();

            foreach ($orderProducts as $orderProduct) {
                $orderProduct->setStatus($status);
            }
        }
        
        foreach ($updatedOrderProducts as $updatedOrderProduct) {
            $orderProduct = $clonedOrder->exists($updatedOrderProduct);
            if (isset($orderProduct)) {
                $quantityDiff = $updatedOrderProduct->getQuantity() - $orderProduct->getQuantity();
                $product = $updatedOrderProduct->getProduct();
                $product->setQuantity($orderProduct->getProduct()->getQuantity() - $quantityDiff);
                $this->entityManager->persist($product);
            } else {
                $product = $updatedOrderProduct->getProduct();
                $product->setQuantity($updatedOrderProduct->getProduct()->getQuantity() - $updatedOrderProduct->getQuantity());
                $this->entityManager->persist($product);
            }
        }
        foreach ($clonedOrder->getOrderProducts() as $clonedProduct) {
            if (null == $order->exists($clonedProduct)) {
                $removedProduct = $this->productRepository->find($clonedProduct->getProduct()->getId());
                $removedProduct->setQuantity($clonedProduct->getProduct()->getQuantity() + $clonedProduct->getQuantity());
                $this->entityManager->persist($removedProduct);
            }
        }

        $this->entityManager->flush();
    }
}
