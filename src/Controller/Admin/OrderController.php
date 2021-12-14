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
use App\Entity\Product;
use App\Form\SellerOrderType;
use App\Form\AdminOrderType;
use App\Form\AddProductToOrderType;
use App\Manager\CartManager;
use Symfony\Component\Form\FormError;

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
            'orders' => $orderRepository->findByNot('status', Order::STATUS_CART),
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
        $form = $this->createForm(AdminOrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $form->get('status')->getData();

            $order->setStatus($status);
                
            $orderProducts = $order->getOrderProducts();

            foreach ($orderProducts as $orderProduct) {
                $orderProduct->setStatus($status);
            }

            $entityManager->flush();

            return $this->redirectToRoute('admin_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/product", name="admin_product_add", methods={"GET","POST"}, host="admin.%domain%")
     */
    public function addProduct(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AddProductToOrderType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();
            $product = $item->getProduct();
            if ($product->getQuantity() > 0 && $product->getQuantity() > $form->get('quantity')->getData()) {
                $item->setProductOrder($order);
                $order
                    ->addOrderProduct($item)
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime());
    
                $entityManager->persist($order);
                $entityManager->flush();
    
                return $this->redirectToRoute('admin_order_edit', ['id' => $order->getId()]);
            }
            $form->get('quantity')->addError(new FormError('Invalid quantity! Must be in range 0 to '. $product->getQuantity()));
        }

        return $this->render('admin/order/product_add.html.twig', [
            'order' => $order,
            'form' => $form->createView()
        ]);
    }
}
