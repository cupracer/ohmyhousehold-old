<?php

namespace App\Security\Voter\Account;

use App\Entity\HouseholdUser;
use App\Entity\User;
use App\Entity\AssetAccount;
use App\Repository\HouseholdUserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AssetAccountVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const USE = 'use';

    private HouseholdUserRepository $householdUserRepository;

    public function __construct(HouseholdUserRepository $householdUserRepository)
    {
        $this->householdUserRepository = $householdUserRepository;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::USE])) {
            return false;
        }

        // only vote on `AssetAccount` objects
        if (!$subject instanceof AssetAccount) {
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

        // you know $subject is a AssetAccount object, thanks to `supports()`
        /** @var AssetAccount $assetAccount */
        $assetAccount = $subject;

        $householdUser = $this->householdUserRepository->findOneBy([
            'user' => $user,
            'household' => $assetAccount->getHousehold()
        ]);

        if (!$householdUser instanceof HouseholdUser) {
            // the user must be a valid householdUser; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($householdUser);
            case self::EDIT:
                return $this->canEdit($householdUser, $assetAccount);
            case self::DELETE:
                return $this->canDelete($householdUser, $assetAccount);
            case self::USE:
                return $this->canUse($householdUser, $assetAccount);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return (bool)$householdUser;
    }

    private function canEdit(HouseholdUser $householdUser, AssetAccount $assetAccount): bool
    {
        return $householdUser->getIsAdmin() || in_array($householdUser, $assetAccount->getOwners()->toArray());
    }

    private function canDelete(HouseholdUser $householdUser, AssetAccount $assetAccount): bool
    {
        // if users can edit, they can delete as well
        return $this->canEdit($householdUser, $assetAccount);
    }

    private function canUse(HouseholdUser $householdUser, AssetAccount $assetAccount): bool
    {
        // if users can edit, they can use as well
        return $this->canEdit($householdUser, $assetAccount);
    }
}