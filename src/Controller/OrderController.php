<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\OrderRepository;
use App\Repository\OrderProductRepository;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Form\SellerOrderType;

/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/", name="seller_order_index", methods={"GET"}, host="seller.%domain%")
     * @IsGranted("ROLE_SELLER")
     */
    public function index(OrderRepository $orderRepository, OrderProductRepository $orderProductRepository): Response
    {
        $productIds = $this->getUser()->getProductIds();
        $orderIds = $orderProductRepository->getOrderIdsHavingProducts($productIds);

        return $this->render('seller/order/index.html.twig', [
            'orders' => $orderRepository->findByIn('id', $orderIds),
        ]);
    }

    /**
     * @Route("/{id}", name="order_show", methods={"GET"}, host="seller.%domain%")
     * @IsGranted("ROLE_SELLER")
     */
    public function show(Order $order): Response
    {
        return $this->render('seller/order/show.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="seller_order_edit", methods={"GET","POST"}, host="seller.%domain%")
     * @IsGranted("ROLE_SELLER")
     */
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager, OrderProductRepository $orderProductRepository): Response
    {
        $form = $this->createForm(SellerOrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $form->get('status')->getData();

            if ($order->getStatus() === 'new' && $status === 'inprogress') {
                $order->setStatus('inprogress');

                $productIds = $this->getUser()->getProductIds();
                $orderProducts = $orderProductRepository->getOrderProductsHavingProducts($order->getId(), $productIds);

                foreach ($orderProducts as $orderProduct) {
                    $orderProduct->setStatus(OrderProduct::STATUS_INPROGRESS);
                }
            } else if ($order->getStatus() === 'inprogress' && $status === 'delivered') {
                $productIds = $this->getUser()->getProductIds();
                $orderProducts = $orderProductRepository->getOrderProductsHavingProducts($order->getId(), $productIds);

                foreach ($orderProducts as $orderProduct) {
                    $orderProduct->setStatus(OrderProduct::STATUS_COMPLETE);
                }

                $allOrderProducts = $orderProductRepository->findBy(['order_id' => $order->getId()]);
                $allDelivered = true;
                foreach ($allOrderProducts as $singleOrderProduct) {
                    if ($singleOrderProduct->getStatus() !== OrderProduct::STATUS_COMPLETE) {
                        $allDelivered = false;
                        break;
                    }
                }
                if ($allDelivered) {
                    $order->setStatus('delivered');
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('seller_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('seller/order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/history", name="orders_history", methods={"GET"}, host="buyer.%domain%")
     * @IsGranted("ROLE_BUYER")
     */
    public function buyerOrdersIndex(OrderRepository $orderRepository): Response
    {
        return $this->render('buyer/order/index.html.twig', [
            'orders' => $orderRepository->findBy([
                'user_id' => $this->getUser()->getId()
            ]),
        ]);
    }

    /**
     * @Route("/{id}", name="buyer_order_show", methods={"GET"}, host="buyer.%domain%")
     * @IsGranted("ROLE_BUYER")
     * @IsGranted("show", subject="order")
     */
    public function buyerOrderShow(Order $order): Response
    {
        return $this->render('buyer/order/show.html.twig', [
            'order' => $order,
        ]);
    }
}
