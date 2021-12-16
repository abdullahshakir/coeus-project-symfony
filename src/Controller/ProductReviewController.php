<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Form\ProductFeedbackType;
use App\Entity\ProductFeedback;
use Doctrine\ORM\EntityManagerInterface;
/**
 * @Route("product/review")
 */
class ProductReviewController extends AbstractController
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @Route("/{token}", name="product_review", methods={"GET","POST"}, host="buyer.%domain%")
     */
    public function index(Request $request, string $token, EntityManagerInterface $entityManager, OrderRepository $orderRepository): Response
    {
        $productFeedback = new ProductFeedback();
        $form = $this->createForm(ProductFeedbackType::class, $productFeedback);
        $form->handleRequest($request);

        $errors = [];
        $unreviewedProduct = null;
        $order = $orderRepository->findOneBy([
            'token' => $token
        ]);

        if (!$order) {
            $errors['invalid_order_token'] = true;
        } else {
            $unreviewedProduct = $this->get_unreviewed_products($order);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $this->productRepository->find($form->get('productId')->getData());
            $productFeedback->setProduct($product);
            $productFeedback->setCreatedAt(new \DateTimeImmutable());
            $productFeedback->setReviewOrder($order);

            $entityManager->persist($productFeedback);
            $entityManager->flush();

            return $this->redirectToRoute('product_review', [
                'token' => $order->getToken()
            ]);
        }   

        return $this->render('buyer/product_review/index.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
            'unreviewedProduct' => $unreviewedProduct,
            'errors' => $errors
        ]);
    }

    private function get_unreviewed_products($order)
    {
        $reviewedProductIds = $order->getReviewedProductIds();
        $orderProductIds = $order->getOrderProductsIds();
        $unReviewedProductIds = array_diff($orderProductIds, $reviewedProductIds); 
        return (!empty($unReviewedProductIds)) ? $this->productRepository->find(current($unReviewedProductIds)) : null;
    }
}
