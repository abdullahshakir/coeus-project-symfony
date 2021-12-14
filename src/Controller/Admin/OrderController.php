<?php

namespace App\Controller\Admin;

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
     * @Route("/", name="admin_order_index", methods={"GET"}, host="admin.%domain%")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(OrderRepository $orderRepository, OrderProductRepository $orderProductRepository): Response
    {
        return $this->render('admin/order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_order_show", methods={"GET"}, host="admin.%domain%")
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(Order $order): Response
    {
        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_order_edit", methods={"GET","POST"}, host="admin.%domain%")
     * @IsGranted("ROLE_ADMIN")
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

            return $this->redirectToRoute('admin_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }
}
