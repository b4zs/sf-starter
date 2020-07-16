<?php


namespace Core\UserBundle\Security;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use FOS\UserBundle\Model\UserInterface;

class OwnUserVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return $subject instanceof UserInterface
            && in_array($attribute, ['EDIT', 'VIEW',]);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        return $user instanceof UserInterface
            && $subject instanceof UserInterface
            && $subject->getId() === $user->getId();
    }

}
