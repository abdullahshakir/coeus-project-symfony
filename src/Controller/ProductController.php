<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Form\AddToCartType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\OrderProductRepository;
use App\Repository\ProductFeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Manager\CartManager;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="product_index", methods={"GET"}, host="seller.%domain%")
     * @IsGranted("ROLE_SELLER")
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('seller/product/index.html.twig', [
            'products' => $productRepository->findBy(['user' => $this->getUser()]),
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"}, host="seller.%domain%")
     */
    public function new(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository, LoggerInterface $logger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUser($this->getUser());
            $product->setCategory($form->get('cat')->getData());

            $file = $form->get('image')->getData();

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $newFilename = md5(uniqid()).'.'.$file->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $file->move(
                        $this->getParameter('product_images_directory'),
                        $newFilename
                    );

                    $product->setImageLink($newFilename);
                } catch (FileException $e) {
                    $logger->error(sprintf('%s:%s', get_class($e), $e->getMessage()));
                    throw $e;
                }
                
                unset($file);
            } else {
                $product->setImageLink('default_product.png');
            }

            $logger->info(sprintf('New product %s added.', $product->getName()));

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('seller/product/new.html.twig', [
            'product' => $product,
            'form' => $form
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"}, host="seller.%domain%")
     * @IsGranted("ROLE_SELLER")
     */
    public function show(Product $product, OrderProductRepository $orderProductRepository, ProductFeedbackRepository $productFeedbackRepository): Response
    {
        return $this->render('seller/product/show.html.twig', [
            'product' => $product,
            'data' => [
                'totalSales' => $orderProductRepository->getTotalSales($product),
                'feedback' => $productFeedbackRepository->findBy(['product_id' => $product->getId()]),
            ]
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"}, host="seller.%domain%")
     */
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setCategory($form->get('cat')->getData());

            $file = $form->get('image')->getData();

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $newFilename = md5(uniqid()).'.'.$file->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $file->move(
                        $this->getParameter('product_images_directory'),
                        $newFilename
                    );

                    $product->setImageLink($newFilename);
                } catch (FileException $e) {
                    throw $e;
                }
                
                unset($file);
            }

            $entityManager->flush();

            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('seller/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"POST"}, host="seller.%domain%")
     * @IsGranted("ROLE_SELLER")
     */
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager, Filesystem $filesystem): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $filesystem->remove($this->getParameter('product_images_directory'). '/' .$product->getImageLink());
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}", name="buyer_product_show", methods={"GET","POST"}, host="buyer.%domain%")
     */
    public function showProduct(Request $request, Product $product, OrderProductRepository $orderProductRepository, CartManager $cartManager): Response
    {
        $form = $this->createForm(AddToCartType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();
            $item->setProduct($product);

            $cart = $cartManager->getCurrentCart();
            $cart
                ->addOrderProduct($item)
                ->setUpdatedAt(new \DateTime());

            $cartManager->save($cart);

            return $this->redirectToRoute('cart');
        }

        return $this->render('buyer/product/show.html.twig', [
            'product' => $product,
            'data' => [
                'totalSales' => $orderProductRepository->getTotalSales($product),
            ],
            'form' => $form->createView()
        ]);
    }
}
