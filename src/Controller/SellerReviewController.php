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
    /**
     * @Route("/{token}", name="seller_review", methods={"GET","POST"}, host="buyer.%domain%")
     */
    public function index(Request $request, string $token, EntityManagerInterface $entityManager, OrderRepository $orderRepository, UserRepository $userRepository): Response
    {
        $userFeedback = new UserFeedback();
        $form = $this->createForm(UserFeedbackType::class, $userFeedback);
        $form->handleRequest($request);

        $errors = [];
        $orderSeller = null;
        $order = $orderRepository->findOneBy([
            'token' => $token
        ]);

        if (!isset($order)) {
            $errors['invalid_order_token'] = true;
        } else {
            $reviewedUsers = $order->getReviewedUserIds();
            $orderSellers = $order->getSellers();
            $unReviewedSellers = array_diff($orderSellers, $reviewedUsers); 
            
            if (empty($unReviewedSellers)) {
                return $this->redirectToRoute('product_review', [
                    'token' => $order->getToken()
                ]);
            }

            $orderSeller = (!empty($unReviewedSellers)) ? $userRepository->find(current($unReviewedSellers)) : null;
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $seller = $userRepository->find($form->get('userId')->getData());
            $userFeedback->setUser($seller);
            $userFeedback->setCreatedAt(new \DateTimeImmutable());
            $userFeedback->setReviewOrder($order);

            $entityManager->persist($userFeedback);
            $entityManager->flush();

            return $this->redirectToRoute('seller_review', [
                'token' => $order->getToken()
            ]);
        }   

        return $this->render('buyer/seller_review/index.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
            'orderSeller' => $orderSeller,
            'errors' => $errors
        ]);
    }
}
