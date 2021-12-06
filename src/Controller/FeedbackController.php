<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserFeedbackRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/feedback")
 */
class FeedbackController extends AbstractController
{
    /**
     * @Route("/", name="seller_feedback_index", methods={"GET"})
     * @IsGranted("ROLE_SELLER")
     */
    public function index(UserFeedbackRepository $userFeedbackRepository): Response
    {
        return $this->render('seller/feedback/index.html.twig', [
            'feedbacks' => $userFeedbackRepository->findBy([
                'user_id' => $this->getUser()->getId()
            ]),
        ]);
    }
}
