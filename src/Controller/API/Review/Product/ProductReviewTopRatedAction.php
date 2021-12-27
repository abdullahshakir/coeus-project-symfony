<?php

namespace App\Controller\API\Review\Product;

use App\Entity\Product;
use App\Repository\ProductFeedbackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductReviewTopRatedAction extends AbstractController
{
    private $productFeedbackRepository;

    public function __construct(ProductFeedbackRepository $productFeedbackRepository)
    {
        $this->productFeedbackRepository = $productFeedbackRepository;
    }

    public function __invoke()
    {
        return $this->productFeedbackRepository->findTopRatedProductFeedbacks();
    }

}
