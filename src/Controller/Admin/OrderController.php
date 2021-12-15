<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\OrderProductRepository;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Form\SellerOrderType;
use App\Form\AdminOrderType;
use App\Form\AddProductToOrderType;
use App\Manager\CartManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

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
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager, OrderProductRepository $orderProductRepository, ProductRepository $productRepository): Response
    {
        if (null == $this->getSession()->get('clonedOrder')) {
            $this->getSession()->set('clonedOrder', $order);
        }
        
        $form = $this->createForm(AdminOrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $clonedOrder = $this->getSession()->get('clonedOrder');
            $this->getSession()->remove('clonedOrder');
            $updatedOrderProducts = $form->get('orderProducts')->getData()->toArray();

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
                    $entityManager->persist($product);
                } else {
                    $product = $updatedOrderProduct->getProduct();
                    $product->setQuantity($updatedOrderProduct->getProduct()->getQuantity() - $updatedOrderProduct->getQuantity());
                    $entityManager->persist($product);
                }
            }
            foreach ($clonedOrder->getOrderProducts() as $clonedProduct) {
                if (null == $order->exists($clonedProduct)) {
                    $removedProduct = $productRepository->find($clonedProduct->getProduct()->getId());
                    $removedProduct->setQuantity($clonedProduct->getProduct()->getQuantity() + $clonedProduct->getQuantity());
                    // dd($removedProduct);
                    $entityManager->persist($removedProduct);
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
