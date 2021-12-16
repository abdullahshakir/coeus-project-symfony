<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Form\UserFeedbackType;
use App\Entity\UserFeedback;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("seller/review")
 */
class SellerReviewController extends AbstractController
{
    private $entityManager;
    private $userRepository;
    private $orderRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, OrderRepository $orderRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->orderRepository = $orderRepository;
    }
    
    /**
     * @Route("/{token}", name="seller_review", methods={"GET","POST"}, host="buyer.%domain%")
     */
    public function index(Request $request, string $token): Response
    {
        $userFeedback = new UserFeedback();
        $form = $this->createForm(UserFeedbackType::class, $userFeedback);
        $form->handleRequest($request);

        $errors = [];
        $unReviewedSeller = null;
        $order = $this->orderRepository->findOneBy([
            'token' => $token
        ]);

        if (!isset($order)) {
            $errors['invalid_order_token'] = true;
        } else {
            $unReviewedSeller = $this->get_unreviewed_seller($order);
            if (null == $unReviewedSeller) {
                return $this->redirectToRoute('product_review', [
                    'token' => $order->getToken()
                ]);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $this->save_seller_feedback($form, $userFeedback, $order);
            
            return $this->redirectToRoute('seller_review', [
                'token' => $order->getToken()
            ]);
        }

        return $this->render('buyer/seller_review/index.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
            'unReviewedSeller' => $unReviewedSeller,
            'errors' => $errors
        ]);
    }

    private function get_unreviewed_seller($order)
    {
        $reviewedUserIds = $order->getReviewedUserIds();
        $orderSellerIds = $order->getSellers();
        $unReviewedSellerIds = array_diff($orderSellerIds, $reviewedUserIds); 
        
        return (!empty($unReviewedSellerIds)) ? $this->userRepository->find(current($unReviewedSellerIds)) : null;
    }

    private function save_seller_feedback($form, $userFeedback, $order)
    {
        $seller = $this->userRepository->find($form->get('userId')->getData());
        $userFeedback->setUser($seller);
        $userFeedback->setCreatedAt(new \DateTimeImmutable());
        $userFeedback->setReviewOrder($order);

        $this->entityManager->persist($userFeedback);
        $this->entityManager->flush();
    }
}
