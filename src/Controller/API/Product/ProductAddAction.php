<?php

namespace App\Controller\API\Product;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;

class ProductAddAction extends AbstractController
{
    private $entityManager;
    private $validator;
    private $categoryRepository;
    private $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, CategoryRepository $categoryRepository, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->categoryRepository = $categoryRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(Product $data)
    {
        $this->validator->validate($data);

        $category = $this->categoryRepository->find($data->getCategoryId());
        $data->setCategory($category);
        $data->setUser($this->tokenStorage->getToken()->getUser());
        $this->entityManager->flush();

        return $data;
    }

    public function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
