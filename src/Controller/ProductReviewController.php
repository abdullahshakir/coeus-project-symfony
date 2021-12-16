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
    private $entityManager;
    private $productRepository;
    private $orderRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository, OrderRepository $orderRepository)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Product Review
     * 
     * @param Request $request
     * @param string $token
     * @return Response
     * 
     * @Route("/{token}", name="product_review", methods={"GET","POST"}, host="buyer.%domain%")
     */
    public function index(Request $request, string $token): Response
    {
        $productFeedback = new ProductFeedback();
        $form = $this->createForm(ProductFeedbackType::class, $productFeedback);
        $form->handleRequest($request);

        $errors = [];
        $unreviewedProduct = null;
        $order = $this->orderRepository->findOneBy([
            'token' => $token
        ]);

        if (!$order) {
            $errors['invalid_order_token'] = true;
        } else {
            $unreviewedProduct = $this->get_unreviewed_product($order);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->save_product_feedback($form, $productFeedback, $order);
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

    private function get_unreviewed_product($order)
    {
        $reviewedProductIds = $order->getReviewedProductIds();
        $orderProductIds = $order->getOrderProductsIds();
        $unReviewedProductIds = array_diff($orderProductIds, $reviewedProductIds); 
        return (!empty($unReviewedProductIds)) ? $this->productRepository->find(current($unReviewedProductIds)) : null;
    }

    private function save_product_feedback($form, $productFeedback, $order)
    {
        $product = $this->productRepository->find($form->get('productId')->getData());
        $productFeedback->setProduct($product);
        $productFeedback->setCreatedAt(new \DateTimeImmutable());
        $productFeedback->setReviewOrder($order);

        $this->entityManager->persist($productFeedback);
        $this->entityManager->flush();
    }
}
