<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

class OrderVoter extends Voter
{
    public const EDIT = 'edit';
    public const SHOW = 'show';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::SHOW, self::EDIT], true)
            && $subject instanceof \App\Entity\Order;
    }

    protected function voteOnAttribute(string $attribute, $order, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_BUYER')) {
            switch ($attribute) {
                case self::SHOW:
                    return $user === $order->getUser();
            }
        }

        if ($this->security->isGranted('ROLE_SELLER')) {
            switch ($attribute) {
                case self::SHOW:
                case self::EDIT:
                    return $user === $order->getSeller();
            }
        }
    }
}
