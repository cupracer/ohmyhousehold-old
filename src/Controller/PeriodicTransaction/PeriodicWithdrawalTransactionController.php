<?php

namespace App\Controller\PeriodicTransaction;

use App\Entity\DTO\PeriodicWithdrawalTransactionDTO;
use App\Entity\ExpenseAccount;
use App\Entity\PeriodicWithdrawalTransaction;
use App\Entity\RevenueAccount;
use App\Form\PeriodicTransaction\PeriodicWithdrawalTransactionType;
use App\Repository\Account\ExpenseAccountRepository;
use App\Repository\HouseholdRepository;
use App\Repository\HouseholdUserRepository;
use App\Service\PeriodicTransaction\PeriodicWithdrawalTransactionService;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_HOUSEKEEPINGBOOK")]
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/periodictransaction/withdrawal')]
class PeriodicWithdrawalTransactionController extends AbstractController
{
    private ManagerRegistry $managerRegistry;
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;
    private PeriodicWithdrawalTransactionService $periodicWithdrawalTransactionService;

    public function __construct(HouseholdRepository $householdRepository, RequestStack $requestStack,
                                PeriodicWithdrawalTransactionService $periodicWithdrawalTransactionService,
                                ManagerRegistry $managerRegistry)
    {
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
        $this->periodicWithdrawalTransactionService = $periodicWithdrawalTransactionService;
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/', name: 'housekeepingbook_periodic_withdrawal_transaction_index', methods: ['GET'])]
    public function index(): Response
    {
        $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->render('housekeepingbook/periodictransaction/withdrawal/index.html.twig', [
            'pageTitle' => t('Periodic Withdrawal Transactions'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'housekeepingbook_periodic_withdrawal_transaction_datatables', methods: ['GET'])]
    public function getPeriodicWithdrawalTransactionsAsDatatables(Request $request): Response
    {
        $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->json(
            $this->periodicWithdrawalTransactionService->getPeriodicWithdrawalTransactionsAsDatatablesArray($request, $currentHousehold)
        );
    }

    #[Route('/new', name: 'housekeepingbook_periodic_withdrawal_transaction_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        HouseholdRepository $householdRepository,
        HouseholdUserRepository $householdUserRepository,
        ExpenseAccountRepository $expenseAccountRepository
    ): Response
    {
        $household = null;
        $householdUser = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $household = $householdRepository->find($this->requestStack->getSession()->get('current_household'));
            $householdUser = $householdUserRepository->findOneByUserAndHousehold($this->getUser(), $household);
        }

        $this->denyAccessUnlessGranted('createPeriodicWithdrawalTransaction', $household);

        $periodicWithdrawalTransaction = new PeriodicWithdrawalTransaction();
        $createPeriodicWithdrawalTransaction = new PeriodicWithdrawalTransactionDTO();

        $form = $this->createForm(PeriodicWithdrawalTransactionType::class, $createPeriodicWithdrawalTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->managerRegistry->getManager();

                // Find or create the required revenue account
                $expenseAccount = $expenseAccountRepository->findOneByHouseholdAndAccountHolder($household, $createPeriodicWithdrawalTransaction->getDestination());

                if(!$expenseAccount) {
                    $expenseAccount = new ExpenseAccount();
                    $expenseAccount->setHousehold($household);
                    $expenseAccount->setInitialBalance(0);
                    $expenseAccount->setInitialBalanceDate((new DateTime())->modify('midnight'));
                    $expenseAccount->setAccountHolder($createPeriodicWithdrawalTransaction->getDestination());

                    $entityManager->persist($expenseAccount);
                }

                // explicitly setting to "midnight" might not be necessary for a db date field
                $periodicWithdrawalTransaction->setStartDate($createPeriodicWithdrawalTransaction->getStartDate());
                $periodicWithdrawalTransaction->setEndDate($createPeriodicWithdrawalTransaction->getEndDate());
                $periodicWithdrawalTransaction->setBookingDayOfMonth($createPeriodicWithdrawalTransaction->getBookingDayOfMonth());
                $periodicWithdrawalTransaction->setBookingCategory($createPeriodicWithdrawalTransaction->getBookingCategory());
                $periodicWithdrawalTransaction->setSource($createPeriodicWithdrawalTransaction->getSource());
                $periodicWithdrawalTransaction->setDestination($expenseAccount);
                $periodicWithdrawalTransaction->setAmount($createPeriodicWithdrawalTransaction->getAmount());
                $periodicWithdrawalTransaction->setBookingInterval($createPeriodicWithdrawalTransaction->getBookingInterval());
                $periodicWithdrawalTransaction->setDescription($createPeriodicWithdrawalTransaction->getDescription());
                $periodicWithdrawalTransaction->setPrivate($createPeriodicWithdrawalTransaction->getPrivate());
                $periodicWithdrawalTransaction->setBookingPeriodOffset($createPeriodicWithdrawalTransaction->getBookingPeriodOffset());

                // TODO: Do we need to explicitly check that these values are set and not null?
                $periodicWithdrawalTransaction->setHousehold($household);
                $periodicWithdrawalTransaction->setHouseholdUser($householdUser);

                $entityManager->persist($periodicWithdrawalTransaction);
                $entityManager->flush();

                $this->addFlash('success', t('Periodic withdrawal transaction was added.'));

                return $this->redirectToRoute('housekeepingbook_periodic_withdrawal_transaction_new');
            } catch (Exception $exception) {
                $this->addFlash('error', t('Periodic withdrawal transaction could not be created: ' . $exception->getMessage()));
            }
        }

        return $this->render('housekeepingbook/periodictransaction/withdrawal/form.html.twig', [
            'pageTitle' => t('Add periodic withdrawal transaction'),
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}/edit', name: 'housekeepingbook_periodic_withdrawal_transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PeriodicWithdrawalTransaction $periodicWithdrawalTransaction, ExpenseAccountRepository $expenseAccountRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $periodicWithdrawalTransaction);

        $editPeriodicWithdrawalTransaction = new PeriodicWithdrawalTransactionDTO();
        $editPeriodicWithdrawalTransaction->setStartDate($periodicWithdrawalTransaction->getStartDate());
        $editPeriodicWithdrawalTransaction->setEndDate($periodicWithdrawalTransaction->getEndDate());
        $editPeriodicWithdrawalTransaction->setBookingDayOfMonth($periodicWithdrawalTransaction->getBookingDayOfMonth());
        $editPeriodicWithdrawalTransaction->setBookingCategory($periodicWithdrawalTransaction->getBookingCategory());
        $editPeriodicWithdrawalTransaction->setSource($periodicWithdrawalTransaction->getSource());
        $editPeriodicWithdrawalTransaction->setDestination($periodicWithdrawalTransaction->getDestination()->getAccountHolder());
        $editPeriodicWithdrawalTransaction->setAmount($periodicWithdrawalTransaction->getAmount());
        $editPeriodicWithdrawalTransaction->setBookingInterval($periodicWithdrawalTransaction->getBookingInterval());
        $editPeriodicWithdrawalTransaction->setDescription($periodicWithdrawalTransaction->getDescription());
        $editPeriodicWithdrawalTransaction->setPrivate($periodicWithdrawalTransaction->getPrivate());
        $editPeriodicWithdrawalTransaction->setBookingPeriodOffset($periodicWithdrawalTransaction->getBookingPeriodOffset());

        $form = $this->createForm(PeriodicWithdrawalTransactionType::class, $editPeriodicWithdrawalTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->managerRegistry->getManager();

                // Find or create the required revenue account
                $expenseAccount = $expenseAccountRepository->findOneByHouseholdAndAccountHolder($periodicWithdrawalTransaction->getHousehold(), $editPeriodicWithdrawalTransaction->getDestination());

                if(!$expenseAccount) {
                    $expenseAccount = new RevenueAccount();
                    $expenseAccount->setHousehold($periodicWithdrawalTransaction->getHousehold());
                    $expenseAccount->setInitialBalance(0);
                    $expenseAccount->setInitialBalanceDate((new DateTime())->modify('midnight'));
                    $expenseAccount->setAccountHolder($editPeriodicWithdrawalTransaction->getDestination());

                    $entityManager->persist($expenseAccount);
                }

                $periodicWithdrawalTransaction->setStartDate($editPeriodicWithdrawalTransaction->getStartDate());
                $periodicWithdrawalTransaction->setEndDate($editPeriodicWithdrawalTransaction->getEndDate());
                $periodicWithdrawalTransaction->setBookingDayOfMonth($editPeriodicWithdrawalTransaction->getBookingDayOfMonth());
                $periodicWithdrawalTransaction->setBookingCategory($editPeriodicWithdrawalTransaction->getBookingCategory());
                $periodicWithdrawalTransaction->setSource($editPeriodicWithdrawalTransaction->getSource());
                $periodicWithdrawalTransaction->setDestination($expenseAccount);
                $periodicWithdrawalTransaction->setAmount($editPeriodicWithdrawalTransaction->getAmount());
                $periodicWithdrawalTransaction->setDescription($editPeriodicWithdrawalTransaction->getDescription());
                $periodicWithdrawalTransaction->setBookingInterval($editPeriodicWithdrawalTransaction->getBookingInterval());
                $periodicWithdrawalTransaction->setPrivate($editPeriodicWithdrawalTransaction->getPrivate());
                $periodicWithdrawalTransaction->setBookingPeriodOffset($editPeriodicWithdrawalTransaction->getBookingPeriodOffset());

                $entityManager->flush();
                $this->addFlash('success', t('Periodic withdrawal transaction was updated.'));

                return $this->redirectToRoute('housekeepingbook_periodic_withdrawal_transaction_index');
            } catch (Exception) {
                $this->addFlash('error', t('Periodic withdrawal transaction could not be updated.'));
            }
        }

        return $this->render('housekeepingbook/periodictransaction/withdrawal/form.html.twig', [
            'pageTitle' => t('Edit periodic withdrawal transaction'),
            'form' => $form->createView(),
            'periodicWithdrawalTransaction' => $periodicWithdrawalTransaction,
            'button_label' => t('Update'),
        ]);
    }


    #[Route('/{id}', name: 'housekeepingbook_periodic_withdrawal_transaction_delete', methods: ['POST'])]
    public function delete(Request $request, PeriodicWithdrawalTransaction $periodicWithdrawalTransaction): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_periodic_withdrawal_transaction_' . $periodicWithdrawalTransaction->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete', $periodicWithdrawalTransaction);
                $entityManager = $this->managerRegistry->getManager();
                $entityManager->remove($periodicWithdrawalTransaction);
                $entityManager->flush();
                $this->addFlash('success', t('Periodic withdrawal transaction was deleted.'));
            }else {
                throw new Exception('invalid CSRF token');
            }
        }catch (Exception) {
            $this->addFlash('error', t('Periodic withdrawal transaction could not be deleted.'));
        }

        return $this->redirectToRoute('housekeepingbook_periodic_withdrawal_transaction_index');
    }
}
