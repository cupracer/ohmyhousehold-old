<?php

namespace App\Security\Voter\PeriodicTransaction;

use App\Entity\HouseholdUser;
use App\Entity\User;
use App\Entity\PeriodicWithdrawalTransaction;
use App\Repository\HouseholdUserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PeriodicWithdrawalTransactionVoter extends Voter
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

        // only vote on `PeriodicWithdrawalTransaction` objects
        if (!$subject instanceof PeriodicWithdrawalTransaction) {
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

        // you know $subject is a PeriodicWithdrawalTransaction object, thanks to `supports()`
        /** @var PeriodicWithdrawalTransaction $periodicWithdrawalTransaction */
        $periodicWithdrawalTransaction = $subject;

        $householdUser = $this->householdUserRepository->findOneBy([
            'user' => $user,
            'household' => $periodicWithdrawalTransaction->getHousehold()
        ]);

        if (!$householdUser instanceof HouseholdUser) {
            // the user must be a valid householdUser; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($householdUser, $periodicWithdrawalTransaction);
            case self::EDIT:
                return $this->canEdit($householdUser, $periodicWithdrawalTransaction);
            case self::DELETE:
                return $this->canDelete($householdUser, $periodicWithdrawalTransaction);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(HouseholdUser $householdUser, PeriodicWithdrawalTransaction $periodicWithdrawalTransaction): bool
    {
        return ((bool)$householdUser && !$periodicWithdrawalTransaction->getPrivate())
            || $periodicWithdrawalTransaction->getHouseholdUser() === $householdUser;
    }

    private function canEdit(HouseholdUser $householdUser, PeriodicWithdrawalTransaction $periodicWithdrawalTransaction): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return $householdUser->getIsAdmin() || $householdUser === $periodicWithdrawalTransaction->getHouseholdUser();
    }

    private function canDelete(HouseholdUser $householdUser, PeriodicWithdrawalTransaction $periodicWithdrawalTransaction): bool
    {
        // if users can edit, they can delete as well
        return $this->canEdit($householdUser, $periodicWithdrawalTransaction);
    }
}