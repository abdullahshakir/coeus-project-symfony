<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Security\Core\Security;

class RegistrationController extends AbstractController
{
    private $passwordEncoder;
    private $secuirty;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, Security $security)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->security = $security;
    }

    /**
     * @Route("/registration", name="registration", host="%domain%")
     */
    public function index(Request $request)
    {
        // if user is already logged in, don't display the login page again
        if ($this->security->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('user_edit');
        }

        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the new users password
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));

            $accountType = $form->get('accountType')->getData();

            $roles = ($accountType == 'buyer') ? ['ROLE_USER', 'ROLE_BUYER'] : ['ROLE_USER', 'ROLE_SELLER'];

            // Set their roles
            $user->setRoles($roles);

            $user->setStatus(User::STATUS_ACTIVE);

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
