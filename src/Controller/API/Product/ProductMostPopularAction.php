<?php

namespace App\Controller\API\Product;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\ProductFeedbackRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductMostPopularAction extends AbstractController
{
    private $productFeedbackRepository;
    private $productRepository;

    public function __construct(ProductFeedbackRepository $productFeedbackRepository, ProductRepository $productRepository)
    {
        $this->productFeedbackRepository = $productFeedbackRepository;
        $this->productRepository = $productRepository;
    }

    public function __invoke()
    {
        return $this->find_most_popular_products();
    }

    private function find_most_popular_products()
    {
        $mostPopularFeedbackProducts = $this->productFeedbackRepository->findMostPopularFeedbackProducts();
        $mostPopularFeedbackProductIds = [];

        foreach ($mostPopularFeedbackProducts as $mostPopularFeedbackProduct) {
            $mostPopularFeedbackProductIds[] = $mostPopularFeedbackProduct['product_id'];
        } 
     
        return $this->productRepository->findByIn('id', $mostPopularFeedbackProductIds);
    }
}
