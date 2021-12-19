<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\OrderProductRepository;
use App\Repository\ProductFeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Manager\CartManager;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="admin_product_index", methods={"GET"}, host="admin.%domain%")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('admin/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_product_new", methods={"GET","POST"}, host="admin.%domain%")
     */
    public function new(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
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
                    throw $e;
                }
                
                unset($file);
            } else {
                $product->setImageLink('default_product.png');
            }

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('admin_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form
        ]);
    }

    /**
     * @Route("/{id}", name="admin_product_show", methods={"GET"}, host="admin.%domain%")
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(Product $product, OrderProductRepository $orderProductRepository, ProductFeedbackRepository $productFeedbackRepository): Response
    {
        return $this->render('admin/product/show.html.twig', [
            'product' => $product,
            'data' => [
                'totalSales' => $orderProductRepository->getTotalSales($product),
                'feedback' => $productFeedbackRepository->findBy(['product_id' => $product->getId()]),
            ]
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_product_edit", methods={"GET","POST"}, host="admin.%domain%")
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

            return $this->redirectToRoute('admin_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_product_delete", methods={"POST"}, host="admin.%domain%")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager, Filesystem $filesystem): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $filesystem->remove($this->getParameter('product_images_directory'). '/' .$product->getImageLink());
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
