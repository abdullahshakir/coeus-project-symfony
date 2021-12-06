<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\OrderRepository;
use App\Entity\Order;
use App\Form\SellerOrderType;

/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/", name="seller_order_index", methods={"GET"})
     * @IsGranted("ROLE_SELLER")
     */
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('seller/order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="order_show", methods={"GET"})
     * @IsGranted("ROLE_SELLER")
     */
    public function show(Order $order): Response
    {
        return $this->render('seller/order/show.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="seller_order_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SellerOrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('seller_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('seller/order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }
}
