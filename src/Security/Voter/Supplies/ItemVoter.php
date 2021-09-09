<?php

namespace App\Security\Voter\Supplies;

use App\Entity\HouseholdUser;
use App\Entity\Supplies\Item;
use App\Entity\User;
use App\Repository\HouseholdUserRepository;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ItemVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const CHECKOUT = 'checkout';
    const CHECKIN = 'checkin';

    private HouseholdUserRepository $householdUserRepository;

    public function __construct(HouseholdUserRepository $householdUserRepository)
    {
        $this->householdUserRepository = $householdUserRepository;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::CHECKOUT, self::CHECKIN, ])) {
            return false;
        }

        // only vote on `Item` objects
        if (!$subject instanceof Item) {
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

        // you know $subject is an Item object, thanks to `supports()`
        /** @var Item $item */
        $item = $subject;

        $householdUser = $this->householdUserRepository->findOneBy([
            'user' => $user,
            'household' => $item->getHousehold()
        ]);

        if (!$householdUser instanceof HouseholdUser) {
            // the user must be a valid householdUser; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($householdUser);
            case self::EDIT:
                return $this->canEdit($householdUser);
            case self::DELETE:
                return $this->canDelete($householdUser);
            case self::CHECKOUT:
                return $this->canCheckout($householdUser);
            case self::CHECKIN:
                return $this->canCheckin($householdUser);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canView(HouseholdUser $householdUser): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return (bool)$householdUser;
    }

    private function canEdit(HouseholdUser $householdUser): bool
    {
        // no further permissions than 'view' required to edit items
        return $this->canView($householdUser);
    }

    private function canDelete(HouseholdUser $householdUser): bool
    {
        return $householdUser->getIsAdmin();
    }

    private function canCheckout(HouseholdUser $householdUser): bool
    {
        // if users can edit, they can checkout
        return $this->canEdit($householdUser);
    }

    private function canCheckin(HouseholdUser $householdUser): bool
    {
        // if users can checkout, they can checkin
        return $this->canCheckout($householdUser);
    }
}