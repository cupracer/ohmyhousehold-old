<?php

namespace App\Security\Voter\Transaction;

use App\Entity\HouseholdUser;
use App\Entity\User;
use App\Entity\TransferTransaction;
use App\Repository\HouseholdUserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TransferTransactionVoter extends Voter
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

        // only vote on `TransferTransaction` objects
        if (!$subject instanceof TransferTransaction) {
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

        // you know $subject is a TransferTransaction object, thanks to `supports()`
        /** @var TransferTransaction $transferTransaction */
        $transferTransaction = $subject;

        $householdUser = $this->householdUserRepository->findOneBy([
            'user' => $user,
            'household' => $transferTransaction->getHousehold()
        ]);

        if (!$householdUser instanceof HouseholdUser) {
            // the user must be a valid householdUser; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($householdUser, $transferTransaction);
            case self::EDIT:
                return $this->canEdit($householdUser, $transferTransaction);
            case self::DELETE:
                return $this->canDelete($householdUser, $transferTransaction);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(HouseholdUser $householdUser, TransferTransaction $transferTransaction): bool
    {
        return ((bool)$householdUser && !$transferTransaction->getPrivate())
            || $transferTransaction->getHouseholdUser() === $householdUser;
    }

    private function canEdit(HouseholdUser $householdUser, TransferTransaction $transferTransaction): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return $householdUser->getIsAdmin() || $householdUser === $transferTransaction->getHouseholdUser();
    }

    private function canDelete(HouseholdUser $householdUser, TransferTransaction $transferTransaction): bool
    {
        // if users can edit, they can delete as well
        return $this->canEdit($householdUser, $transferTransaction);
    }
}