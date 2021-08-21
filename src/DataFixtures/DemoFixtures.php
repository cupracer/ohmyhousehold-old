<?php

namespace App\DataFixtures;

use App\Entity\AccountHolder;
use App\Entity\AssetAccount;
use App\Entity\BookingCategory;
use App\Entity\DepositTransaction;
use App\Entity\ExpenseAccount;
use App\Entity\Household;
use App\Entity\HouseholdUser;
use App\Entity\RevenueAccount;
use App\Entity\TransferTransaction;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Entity\WithdrawalTransaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DemoFixtures extends Fixture
{
    private string $superAdminEmail;
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(string $superAdminEmail, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->superAdminEmail = $superAdminEmail;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $adminProfile = (new UserProfile())
            ->setForenames('Admin')
            ->setSurname('Admin')
            ->setLocale('de');

        $admin = new User();
        $admin
            ->setRoles([
                User::ROLES['ROLE_SUPER_ADMIN'],
                User::ROLES['ROLE_API'],
                User::ROLES['ROLE_HOUSEKEEPINGBOOK'],
            ])
            ->setIsVerified(true)
            ->setEmail(strtolower($this->superAdminEmail))
            ->setPassword(
                $this->passwordEncoder->encodePassword(
                    $admin,
                    'secret'
                ))
            ->setUserProfile($adminProfile);

        $household = (new Household())
            ->setTitle($admin->getEmail());

        $adminHouseholdUser = (new HouseholdUser())
            ->setUser($admin)
            ->setHousehold($household)
            ->setIsAdmin(true);

        $accountHolder1 = (new AccountHolder())
            ->setHousehold($household)
            ->setName('AccountHolder1');

        $accountHolder2 = (new AccountHolder())
            ->setHousehold($household)
            ->setName('AccountHolder2');

        $bookingCategory1 = (new BookingCategory())
            ->setHousehold($household)
            ->setName('BookingCategory1');

        $bookingCategory2 = (new BookingCategory())
            ->setHousehold($household)
            ->setName('BookingCategory2');

        $bookingCategory3 = (new BookingCategory())
            ->setHousehold($household)
            ->setName('BookingCategory3');

        $assetAccount1 = (new AssetAccount())
            ->setInitialBalance(0)
            ->setName('Giro1')
            ->setHousehold($household)
            ->addOwner($adminHouseholdUser);

        $assetAccount2 = (new AssetAccount())
            ->setInitialBalance(0)
            ->setName('Spar1')
            ->setHousehold($household)
            ->addOwner($adminHouseholdUser);

        $revenueAccount = (new RevenueAccount())
            ->setHousehold($household)
            ->setInitialBalance(0)
            ->setAccountHolder($accountHolder1);

        $expenseAccount = (new ExpenseAccount())
            ->setHousehold($household)
            ->setInitialBalance(0)
            ->setAccountHolder($accountHolder2);

        $depositTransaction = (new DepositTransaction())
            ->setHousehold($household)
            ->setHouseholdUser($adminHouseholdUser)
            ->setSource($revenueAccount)
            ->setDestination($assetAccount1)
            ->setAmount(1000)
            ->setBookingDate(new \DateTime())
            ->setBookingCategory($bookingCategory1)
            ->setDescription('income1')
            ->setPrivate(false);

        $withdrawalTransaction = (new WithdrawalTransaction())
            ->setHousehold($household)
            ->setHouseholdUser($adminHouseholdUser)
            ->setSource($assetAccount1)
            ->setDestination($expenseAccount)
            ->setAmount(22,88)
            ->setBookingDate(new \DateTime())
            ->setBookingCategory($bookingCategory2)
            ->setDescription('expense1')
            ->setPrivate(false);

        $transferTransaction = (new TransferTransaction())
            ->setHousehold($household)
            ->setHouseholdUser($adminHouseholdUser)
            ->setSource($assetAccount1)
            ->setDestination($assetAccount2)
            ->setAmount(290)
            ->setBookingDate(new \DateTime())
            ->setBookingCategory($bookingCategory3)
            ->setDescription('transfer1')
            ->setPrivate(false);

        //2nd user

        $member1Profile = (new UserProfile())
            ->setForenames('Member1')
            ->setSurname('Member1')
            ->setLocale('de');

        $member1 = new User();
        $member1
            ->setRoles([
                User::ROLES['ROLE_HOUSEKEEPINGBOOK'],
            ])
            ->setIsVerified(true)
            ->setEmail(strtolower('member1@example.com'))
            ->setPassword(
                $this->passwordEncoder->encodePassword(
                    $member1,
                    'secret'
                ))
            ->setUserProfile($member1Profile);

        $member1HouseholdUser = (new HouseholdUser())
            ->setUser($member1)
            ->setHousehold($household)
            ->setIsAdmin(false);

        // save all

        $manager->persist($household);
        $manager->persist($admin);
        $manager->persist($adminHouseholdUser);
        $manager->persist($accountHolder1);
        $manager->persist($accountHolder2);
        $manager->persist($bookingCategory1);
        $manager->persist($bookingCategory2);
        $manager->persist($bookingCategory3);
        $manager->persist($assetAccount1);
        $manager->persist($assetAccount2);
        $manager->persist($revenueAccount);
        $manager->persist($expenseAccount);
        $manager->persist($depositTransaction);
        $manager->persist($withdrawalTransaction);
        $manager->persist($transferTransaction);
        $manager->persist($member1);
        $manager->persist($member1HouseholdUser);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['demo',];
    }
}
