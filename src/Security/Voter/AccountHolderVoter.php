<?php

namespace App\Security\Voter;

use App\Entity\AccountHolder;
use App\Entity\DynamicBooking;
use App\Entity\HouseholdUser;
use App\Entity\User;
use App\Repository\HouseholdUserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccountHolderVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // only vote on `AccountHolder` objects
        if (!$subject instanceof AccountHolder) {
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

        // you know $subject is an AccountHolder object, thanks to `supports()`
        /** @var AccountHolder $accountHolder */
        $accountHolder = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($accountHolder, $user);
            case self::EDIT:
                return $this->canEdit($accountHolder, $user);
            case self::DELETE:
                return $this->canDelete($accountHolder, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(AccountHolder $accountHolder, User $user): bool
    {
        foreach($accountHolder->getHousehold()->getHouseholdUsers() as $householdUser) {
            if($householdUser->getUser() === $user) {
                return true;
            }
        }

        return false;
    }

    private function canEdit(AccountHolder $accountHolder, User $user): bool
    {
        // no further permissions than 'view' required to edit account holders
        return $this->canView($accountHolder, $user);
    }

    private function canDelete(AccountHolder $accountHolder, User $user): bool
    {
        // if users can edit, they can delete as well
        return $this->canEdit($accountHolder, $user);
    }
}