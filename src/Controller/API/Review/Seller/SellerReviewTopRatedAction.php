<?php

namespace App\Controller\API\Review\Seller;

use App\Repository\UserFeedbackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SellerReviewTopRatedAction extends AbstractController
{
    private $productFeedbackRepository;

    public function __construct(UserFeedbackRepository $userFeedbackRepository)
    {
        $this->userFeedbackRepository = $userFeedbackRepository;
    }

    public function __invoke()
    {
        return $this->userFeedbackRepository->findTopRatedSellerFeedbacks();
    }

}
