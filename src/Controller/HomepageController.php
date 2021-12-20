<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\CategoryRepository;

class HomepageController extends AbstractController
{
    /**
     * @Route(
     *     "/{_locale}/",
     *     name="seller_homepage",
     *     host="seller.{domain}",
     *     defaults={"domain"="%domain%"},
     *     requirements={"domain"="%domain%", "_locale": "en|de"}
     * )
     * @IsGranted("ROLE_SELLER")
     */
    public function sellerHomepageAction()
    {
        return $this->render('seller/dashboard.html.twig');
    }

    /**
     * @Route(
     *     "/",
     *     name="buyer_homepage",
     *     host="buyer.{domain}",
     *     defaults={"domain"="%domain%"},
     *     requirements={"domain"="%domain%"}
     * )
     * 
     */
    public function buyerHomepageAction(CategoryRepository $categoryRepository)
    {
        return $this->render('buyer/dashboard.html.twig', [
            'categories' => $categoryRepository->findAll()
        ]);
    }

    /**
     * @Route(
     *     "/",
     *     name="admin_homepage",
     *     host="admin.{domain}",
     *     defaults={"domain"="%domain%"},
     *     requirements={"domain"="%domain%"}
     * )
     * 
     */
    public function adminHomepageAction(CategoryRepository $categoryRepository)
    {
        return $this->render('admin/dashboard.html.twig', [
            'categories' => $categoryRepository->findAll()
        ]);
    }
}
