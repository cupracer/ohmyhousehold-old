<?php

namespace App\Security\Voter;

use App\Entity\Household;
use App\Entity\HouseholdUser;
use App\Entity\User;
use App\Repository\HouseholdUserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class HouseholdVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';

    const CREATE_ASSET_ACCOUNT = 'createAssetAccount';

    const CREATE_DEPOSIT_TRANSACTION = 'createDepositTransaction';
    const CREATE_WITHDRAWAL_TRANSACTION = 'createWithdrawalTransaction';
    const CREATE_TRANSFER_TRANSACTION = 'createTransferTransaction';

    const CREATE_PERIODIC_DEPOSIT_TRANSACTION = 'createPeriodicDepositTransaction';
    const CREATE_PERIODIC_WITHDRAWAL_TRANSACTION = 'createPeriodicWithdrawalTransaction';
    const CREATE_PERIODIC_TRANSFER_TRANSACTION = 'createPeriodicTransferTransaction';

    const CREATE_ACCOUNTHOLDER = 'createAccountHolder';
    const CREATE_BOOKINGCATEGORY = 'createBookingCategory';

    const CREATE_SUPPLIES_BRAND = 'createSuppliesBrand';

    private HouseholdUserRepository $householdUserRepository;

    public function __construct(HouseholdUserRepository $householdUserRepository)
    {
        $this->householdUserRepository = $householdUserRepository;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::VIEW,
            self::EDIT,
            self::CREATE_ASSET_ACCOUNT,
            self::CREATE_DEPOSIT_TRANSACTION,
            self::CREATE_WITHDRAWAL_TRANSACTION,
            self::CREATE_TRANSFER_TRANSACTION,
            self::CREATE_PERIODIC_DEPOSIT_TRANSACTION,
            self::CREATE_PERIODIC_WITHDRAWAL_TRANSACTION,
            self::CREATE_PERIODIC_TRANSFER_TRANSACTION,
            self::CREATE_ACCOUNTHOLDER,
            self::CREATE_BOOKINGCATEGORY,
            self::CREATE_SUPPLIES_BRAND,
        ])) {
            return false;
        }

        // only vote on `Household` objects
        if (!$subject instanceof Household) {
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

        // you know $subject is a Household object, thanks to `supports()`
        /** @var Household $household */
        $household = $subject;

        $householdUser = $this->householdUserRepository->findOneBy(['user' => $user, 'household' => $household]);

        if (!$householdUser instanceof HouseholdUser) {
            // the user must be a valid householdUser; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($householdUser);
            case self::EDIT:
                return $this->canEdit($householdUser);
            case self::CREATE_ASSET_ACCOUNT:
                return $this->canCreateAssetAccount($householdUser);
            case self::CREATE_DEPOSIT_TRANSACTION:
                return $this->canCreateDepositTransaction($householdUser);
            case self::CREATE_WITHDRAWAL_TRANSACTION:
                return $this->canCreateWithdrawalTransaction($householdUser);
            case self::CREATE_TRANSFER_TRANSACTION:
                return $this->canCreateTransferTransaction($householdUser);
            case self::CREATE_PERIODIC_DEPOSIT_TRANSACTION:
                return $this->canCreatePeriodicDepositTransaction($householdUser);
            case self::CREATE_PERIODIC_WITHDRAWAL_TRANSACTION:
                return $this->canCreatePeriodicWithdrawalTransaction($householdUser);
            case self::CREATE_PERIODIC_TRANSFER_TRANSACTION:
                return $this->canCreatePeriodicTransferTransaction($householdUser);
            case self::CREATE_ACCOUNTHOLDER:
                return $this->canCreateAccountHolder($householdUser);
            case self::CREATE_BOOKINGCATEGORY:
                return $this->canCreateBookingCategory($householdUser);
            case self::CREATE_SUPPLIES_BRAND:
                return $this->canCreateSuppliesBrand($householdUser);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return (bool)$householdUser;
    }

    private function canEdit(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return $householdUser->getIsAdmin();
    }

    private function canCreateAssetAccount(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return (bool)$householdUser;
    }

    private function canCreateDepositTransaction(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return (bool)$householdUser;
    }

    private function canCreateWithdrawalTransaction(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return (bool)$householdUser;
    }

    private function canCreateTransferTransaction(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return (bool)$householdUser;
    }

    private function canCreatePeriodicDepositTransaction(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return (bool)$householdUser;
    }

    private function canCreatePeriodicWithdrawalTransaction(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return (bool)$householdUser;
    }

    private function canCreatePeriodicTransferTransaction(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return (bool)$householdUser;
    }

    private function canCreateAccountHolder(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return (bool)$householdUser;
    }

    private function canCreateBookingCategory(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return $householdUser->getIsAdmin();
    }

    private function canCreateSuppliesBrand(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return (bool)$householdUser;
    }
}