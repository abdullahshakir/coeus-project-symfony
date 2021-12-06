<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class HomepageController extends AbstractController
{
    /**
     * @Route(
     *     "/",
     *     name="seller_homepage",
     *     host="seller.{domain}",
     *     defaults={"domain"="%domain%"},
     *     requirements={"domain"="%domain%"}
     * )
     * 
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
    public function buyerHomepageAction()
    {
        return new Response('buyer homepage');
    }
}
