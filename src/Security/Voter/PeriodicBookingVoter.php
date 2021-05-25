<?php

namespace App\Security\Voter;

use App\Entity\HouseholdUser;
use App\Entity\PeriodicBooking;
use App\Entity\User;
use App\Repository\HouseholdUserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PeriodicBookingVoter extends Voter
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

        // only vote on `PeriodicBooking` objects
        if (!$subject instanceof PeriodicBooking) {
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

        // you know $subject is a PeriodicBooking object, thanks to `supports()`
        /** @var PeriodicBooking $periodicBooking */
        $periodicBooking = $subject;

        $householdUser = $this->householdUserRepository->findOneBy([
            'user' => $user,
            'household' => $periodicBooking->getHousehold()
        ]);

        if (!$householdUser instanceof HouseholdUser) {
            // the user must be a valid householdUser; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($householdUser);
            case self::EDIT:
                return $this->canEdit($householdUser, $periodicBooking);
            case self::DELETE:
                return $this->canDelete($householdUser, $periodicBooking);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(HouseholdUser $householdUser): bool
    {
        return (bool)$householdUser;
    }

    private function canEdit(HouseholdUser $householdUser, PeriodicBooking $periodicBooking): bool
    {
        // thanks to voteOnAttribute, we already know that $householdUser belongs to our Household
        return $householdUser->getIsAdmin() || $householdUser === $periodicBooking->getHouseholdUser();
    }

    private function canDelete(HouseholdUser $householdUser, PeriodicBooking $periodicBooking): bool
    {
        // if users can edit, they can delete as well
        return $this->canEdit($householdUser, $periodicBooking);
    }
}