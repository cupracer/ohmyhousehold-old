<?php

namespace App\Security\Voter\Transaction;

use App\Entity\HouseholdUser;
use App\Entity\User;
use App\Entity\WithdrawalTransaction;
use App\Repository\HouseholdUserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class WithdrawalTransactionVoter extends Voter
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

        // only vote on `WithdrawalTransaction` objects
        if (!$subject instanceof WithdrawalTransaction) {
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

        // you know $subject is a WithdrawalTransaction object, thanks to `supports()`
        /** @var WithdrawalTransaction $withdrawalTransaction */
        $withdrawalTransaction = $subject;

        $householdUser = $this->householdUserRepository->findOneBy([
            'user' => $user,
            'household' => $withdrawalTransaction->getHousehold()
        ]);

        if (!$householdUser instanceof HouseholdUser) {
            // the user must be a valid householdUser; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($householdUser, $withdrawalTransaction);
            case self::EDIT:
                return $this->canEdit($householdUser, $withdrawalTransaction);
            case self::DELETE:
                return $this->canDelete($householdUser, $withdrawalTransaction);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(HouseholdUser $householdUser, WithdrawalTransaction $withdrawalTransaction): bool
    {
        return ((bool)$householdUser && !$withdrawalTransaction->getPrivate())
            || $withdrawalTransaction->getHouseholdUser() === $householdUser;
    }

    private function canEdit(HouseholdUser $householdUser, WithdrawalTransaction $withdrawalTransaction): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return $householdUser->getIsAdmin() || $householdUser === $withdrawalTransaction->getHouseholdUser();
    }

    private function canDelete(HouseholdUser $householdUser, WithdrawalTransaction $withdrawalTransaction): bool
    {
        // if users can edit, they can delete as well
        return $this->canEdit($householdUser, $withdrawalTransaction);
    }
}