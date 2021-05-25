<?php

namespace App\Security\Voter;

use App\Entity\DynamicBooking;
use App\Entity\HouseholdUser;
use App\Entity\User;
use App\Repository\HouseholdUserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BookingVoter extends Voter
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

        // only vote on `DynamicBooking` objects
        if (!$subject instanceof DynamicBooking) {
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

        // you know $subject is a DynamicBooking object, thanks to `supports()`
        /** @var DynamicBooking $booking */
        $booking = $subject;

        $householdUser = $this->householdUserRepository->findOneBy([
            'user' => $user,
            'household' => $booking->getHousehold()
        ]);

        if (!$householdUser instanceof HouseholdUser) {
            // the user must be a valid householdUser; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($householdUser, $booking);
            case self::EDIT:
                return $this->canEdit($householdUser, $booking);
            case self::DELETE:
                return $this->canDelete($householdUser, $booking);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(HouseholdUser $householdUser, DynamicBooking $booking): bool
    {
        return ((bool)$householdUser && !$booking->getPrivate())
            || $booking->getHouseholdUser() === $householdUser;
    }

    private function canEdit(HouseholdUser $householdUser, DynamicBooking $booking): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return $householdUser->getIsAdmin() || $householdUser === $booking->getHouseholdUser();
    }

    private function canDelete(HouseholdUser $householdUser, DynamicBooking $booking): bool
    {
        // if users can edit, they can delete as well
        return $this->canEdit($householdUser, $booking);
    }
}