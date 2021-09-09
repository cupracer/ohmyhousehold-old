<?php

namespace App\Security\Voter\PeriodicTransaction;

use App\Entity\HouseholdUser;
use App\Entity\User;
use App\Entity\PeriodicDepositTransaction;
use App\Repository\HouseholdUserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PeriodicDepositTransactionVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    private HouseholdUserRepository $householdUserRepository;

    public function __construct(HouseholdUserRepository $householdUserRepository)
    {
        $this->householdUserRepository = $householdUserRepository;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // only vote on `PeriodicDepositTransaction` objects
        if (!$subject instanceof PeriodicDepositTransaction) {
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

        // you know $subject is a PeriodicDepositTransaction object, thanks to `supports()`
        /** @var PeriodicDepositTransaction $periodicDepositTransaction */
        $periodicDepositTransaction = $subject;

        $householdUser = $this->householdUserRepository->findOneBy([
            'user' => $user,
            'household' => $periodicDepositTransaction->getHousehold()
        ]);

        if (!$householdUser instanceof HouseholdUser) {
            // the user must be a valid householdUser; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($householdUser, $periodicDepositTransaction);
            case self::EDIT:
                return $this->canEdit($householdUser, $periodicDepositTransaction);
            case self::DELETE:
                return $this->canDelete($householdUser, $periodicDepositTransaction);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(HouseholdUser $householdUser, PeriodicDepositTransaction $periodicDepositTransaction): bool
    {
        return !$periodicDepositTransaction->getPrivate() || $periodicDepositTransaction->getHouseholdUser() === $householdUser;
    }

    private function canEdit(HouseholdUser $householdUser, PeriodicDepositTransaction $periodicDepositTransaction): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return $householdUser->getIsAdmin() || $householdUser === $periodicDepositTransaction->getHouseholdUser();
    }

    private function canDelete(HouseholdUser $householdUser, PeriodicDepositTransaction $periodicDepositTransaction): bool
    {
        // if users can edit, they can delete as well
        return $this->canEdit($householdUser, $periodicDepositTransaction);
    }
}