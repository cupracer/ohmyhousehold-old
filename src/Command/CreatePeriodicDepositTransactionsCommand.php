<?php

namespace App\Command;

use App\Entity\DepositTransaction;
use App\Entity\TransferTransaction;
use App\Entity\WithdrawalTransaction;
use App\Repository\HouseholdRepository;
use App\Repository\PeriodicTransaction\PeriodicDepositTransactionRepository;
use App\Repository\PeriodicTransaction\PeriodicTransferTransactionRepository;
use App\Repository\PeriodicTransaction\PeriodicWithdrawalTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreatePeriodicDepositTransactionsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private HouseholdRepository $householdRepository;
    private PeriodicDepositTransactionRepository $periodicDepositTransactionRepository;
    private PeriodicWithdrawalTransactionRepository $periodicWithdrawalTransactionRepository;
    private PeriodicTransferTransactionRepository $periodicTransferTransactionRepository;

    protected static $defaultName = 'app:create:periodic:transactions';

    public function __construct(EntityManagerInterface $entityManager,
                                HouseholdRepository $householdRepository,
                                PeriodicDepositTransactionRepository $periodicDepositTransactionRepository,
                                PeriodicWithdrawalTransactionRepository $periodicWithdrawalTransactionRepository,
                                PeriodicTransferTransactionRepository $periodicTransferTransactionRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->householdRepository = $householdRepository;
        $this->periodicDepositTransactionRepository = $periodicDepositTransactionRepository;
        $this->periodicWithdrawalTransactionRepository = $periodicWithdrawalTransactionRepository;
        $this->periodicTransferTransactionRepository = $periodicTransferTransactionRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates periodic transactions')
            ->addOption('period', null,InputOption::VALUE_REQUIRED, 'A date in the desired period (Y-m-d')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dateInPeriod = null;

        if ($input->getOption('period')) {
            $dateInPeriod = \DateTime::createFromFormat('Y-m-d', $input->getOption('period'));
        }

        if(!$dateInPeriod) {
            $dateInPeriod = new \DateTime();
        }

        $currentPeriodStart = (clone $dateInPeriod)->modify('first day of this month')->modify('midnight');
        $currentPeriodEnd = (clone $dateInPeriod)->modify('first day of next month')->modify('midnight')->modify('-1 second');

        foreach($this->householdRepository->findAll() as $household) {
            $io->info($household->getTitle());

            $periodicDepositTransactions = $this->periodicDepositTransactionRepository->findAllByHouseholdAndDateRangeWithoutTransaction($household, $currentPeriodStart, $currentPeriodEnd);

            foreach($periodicDepositTransactions as $periodicTransaction) {
                $bookingDate = (clone $currentPeriodStart)->modify('- ' . $periodicTransaction->getBookingPeriodOffset() . ' months')->modify('+' . $periodicTransaction->getBookingDayOfMonth()-1 . ' days');

                if($bookingDate > (new \DateTime())->modify('midnight')) {
                    $io->info('skipping future booking');
                    continue;
                }

                $transaction = new DepositTransaction();
                $transaction->setHousehold($household);
                $transaction->setHouseholdUser($periodicTransaction->getHouseholdUser());
                $transaction->setBookingDate($bookingDate);
                $transaction->setBookingCategory($periodicTransaction->getBookingCategory());
                $transaction->setSource($periodicTransaction->getSource());
                $transaction->setDestination($periodicTransaction->getDestination());
                $transaction->setDescription($periodicTransaction->getDescription());
                $transaction->setAmount($periodicTransaction->getAmount());
                $transaction->setPrivate($periodicTransaction->getPrivate());
                $transaction->setBookingPeriodOffset($periodicTransaction->getBookingPeriodOffset());
                $transaction->setPeriodicDepositTransaction($periodicTransaction);

                if ($input->getOption('dry-run')) {
                    $io->info("faking new object: " . $transaction->getDescription());
                }else {
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                    $io->info("saved: " . $transaction->getDescription());
                }
            }

            $periodicWithdrawalTransactions = $this->periodicWithdrawalTransactionRepository->findAllByHouseholdAndDateRangeWithoutTransaction($household, $currentPeriodStart, $currentPeriodEnd);

            foreach($periodicWithdrawalTransactions as $periodicTransaction) {
                $bookingDate = (clone $currentPeriodStart)->modify('- ' . $periodicTransaction->getBookingPeriodOffset() . ' months')->modify('+' . $periodicTransaction->getBookingDayOfMonth()-1 . ' days');

                if($bookingDate > (new \DateTime())->modify('midnight')) {
                    $io->info('skipping future booking');
                    continue;
                }

                $transaction = new WithdrawalTransaction();
                $transaction->setHousehold($household);
                $transaction->setHouseholdUser($periodicTransaction->getHouseholdUser());
                $transaction->setBookingDate($bookingDate);
                $transaction->setBookingCategory($periodicTransaction->getBookingCategory());
                $transaction->setSource($periodicTransaction->getSource());
                $transaction->setDestination($periodicTransaction->getDestination());
                $transaction->setDescription($periodicTransaction->getDescription());
                $transaction->setAmount($periodicTransaction->getAmount());
                $transaction->setPrivate($periodicTransaction->getPrivate());
                $transaction->setBookingPeriodOffset($periodicTransaction->getBookingPeriodOffset());
                $transaction->setPeriodicWithdrawalTransaction($periodicTransaction);

                if ($input->getOption('dry-run')) {
                    $io->info("faking new object: " . $transaction->getDescription());
                }else {
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                    $io->info("saved: " . $transaction->getDescription());
                }
            }

            $periodicTransferTransactions = $this->periodicTransferTransactionRepository->findAllByHouseholdAndDateRangeWithoutTransaction($household, $currentPeriodStart, $currentPeriodEnd);

            foreach($periodicTransferTransactions as $periodicTransaction) {
                $bookingDate = (clone $currentPeriodStart)->modify('- ' . $periodicTransaction->getBookingPeriodOffset() . ' months')->modify('+' . $periodicTransaction->getBookingDayOfMonth()-1 . ' days');

                if($bookingDate > (new \DateTime())->modify('midnight')) {
                    $io->info('skipping future booking');
                    continue;
                }

                $transaction = new TransferTransaction();
                $transaction->setHousehold($household);
                $transaction->setHouseholdUser($periodicTransaction->getHouseholdUser());
                $transaction->setBookingDate($bookingDate);
                $transaction->setBookingCategory($periodicTransaction->getBookingCategory());
                $transaction->setSource($periodicTransaction->getSource());
                $transaction->setDestination($periodicTransaction->getDestination());
                $transaction->setDescription($periodicTransaction->getDescription());
                $transaction->setAmount($periodicTransaction->getAmount());
                $transaction->setPrivate($periodicTransaction->getPrivate());
                $transaction->setBookingPeriodOffset($periodicTransaction->getBookingPeriodOffset());
                $transaction->setPeriodicTransferTransaction($periodicTransaction);

                if ($input->getOption('dry-run')) {
                    $io->info("faking new object: " . $transaction->getDescription());
                }else {
                    $this->entityManager->persist($transaction);
                    $this->entityManager->flush();
                    $io->info("saved: " . $transaction->getDescription());
                }
            }
        }

        return 0;
    }
}