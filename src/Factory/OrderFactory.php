<?php


namespace App\Factory;


use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;

/**
 * Class OrderFactory
 * @package App\Factory
 */
class OrderFactory
{
    /**
     * Creates an order.
     *
     * @return Order
     */
    public function create(): Order
    {
        $order = new Order();
        $order
            ->setStatus(Order::STATUS_CART)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        return $order;
    }

    /**
     * Creates an item for a product.
     *
     * @param Product $product
     *
     * @return OrderProduct
     */
    public function createItem(Product $product): OrderProduct
    {
        $orderProduct = new OrderProduct();
        $orderProduct->setProduct($product);
        $orderProduct->setQuantity(1);

        return $item;
    }
}
