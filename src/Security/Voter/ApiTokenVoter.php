<?php

namespace App\Security\Voter;

use App\Entity\ApiToken;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ApiTokenVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::DELETE])) {
            return false;
        }

        // only vote on `ApiToken` objects
        if (!$subject instanceof ApiToken) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a ApiToken object, thanks to `supports()`
        /** @var ApiToken $apiToken */
        $apiToken = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($apiToken, $user);
            case self::DELETE:
                return $this->canDelete($apiToken, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(ApiToken $apiToken, User $user): bool
    {
        // if they can delete, they can view
        return $this->canDelete($apiToken, $user);
    }

    private function canDelete(ApiToken $apiToken, User $user): bool
    {
        return $user === $apiToken->getUser();
    }
}