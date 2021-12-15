<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CartType;
use App\Form\PlaceOrderType;
use App\Manager\CartManager;
use App\Entity\Order;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\EntityManagerInterface;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(Request $request, CartManager $cartManager): Response
    {
        $cart = $cartManager->getCurrentCart();
        $form = $this->createForm(CartType::class, $cart);
        $placeOrderForm = $this->createForm(PlaceOrderType::class, null, [
            'action' => $this->generateUrl('cart_order'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cart->setUpdatedAt(new \DateTime());
            $cartManager->save($cart);

            return $this->redirectToRoute('cart');
        }

        return $this->render('buyer/cart/index.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
            'placeOrderForm' => $placeOrderForm->createView(),
        ]);
    }

    /**
     * @Route("/cart/order", name="cart_order", methods={"POST"})
     */
    public function placeOrder(Request $request, EntityManagerInterface $entityManager, CartManager $cartManager): Response
    {
        $authChecker = $this->get('security.authorization_checker');
        $isRoleBuyer = $authChecker->isGranted('ROLE_BUYER');
        if (!$isRoleBuyer) {
            return $this->render('buyer/cart/authplaceorder.html.twig');
        }

        $cart = $cartManager->getCurrentCart();
        $cart->setUser($this->getUser());
        $cart->setStatus(Order::STATUS_NEW);
        $cart->setUpdatedAt(new \DateTime());
        $cart->setToken(Uuid::v4());
        $cartManager->save($cart);


        $orderProducts = $cart->getOrderProducts();
        foreach ($orderProducts as $orderProduct) {
            $product = $orderProduct->getProduct();
            $product->setQuantity($product->getQuantity() - $orderProduct->getQuantity());
            $entityManager->persist($product);
            $entityManager->flush();
        }
 
        return $this->redirectToRoute('buyer_homepage');
    }
}
