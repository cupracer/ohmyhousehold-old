<?php

namespace App\Security\Voter;

use App\Entity\BookingCategory;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BookingCategoryVoter extends Voter
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

        // only vote on `BookingCategory` objects
        if (!$subject instanceof BookingCategory) {
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

        // you know $subject is a BookingCategory object, thanks to `supports()`
        /** @var BookingCategory $bookingCategory */
        $bookingCategory = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($bookingCategory, $user);
            case self::EDIT:
                return $this->canEdit($bookingCategory, $user);
            case self::DELETE:
                return $this->canDelete($bookingCategory, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(BookingCategory $bookingCategory, User $user): bool
    {
        foreach($bookingCategory->getHousehold()->getHouseholdUsers() as $householdUser) {
            if($householdUser->getUser() === $user) {
                return true;
            }
        }

        return false;
    }

    private function canEdit(BookingCategory $bookingCategory, User $user): bool
    {
        foreach($bookingCategory->getHousehold()->getHouseholdUsers() as $householdUser) {
            if($householdUser->getUser() === $user) {
                if($householdUser->getIsAdmin()) {
                    return true;
                }else {
                    return false;
                }
            }
        }

        return false;
    }

    private function canDelete(BookingCategory $bookingCategory, User $user): bool
    {
        // if users can edit, they can delete as well
        return $this->canEdit($bookingCategory, $user);
    }
}