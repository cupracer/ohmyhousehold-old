<?php

namespace App\Controller\PeriodicTransaction;

use App\Entity\DTO\PeriodicDepositTransactionDTO;
use App\Entity\PeriodicDepositTransaction;
use App\Entity\RevenueAccount;
use App\Form\PeriodicTransaction\PeriodicDepositTransactionType;
use App\Repository\Account\RevenueAccountRepository;
use App\Repository\HouseholdRepository;
use App\Repository\HouseholdUserRepository;
use App\Service\PeriodicTransaction\PeriodicDepositTransactionService;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_HOUSEKEEPINGBOOK")]
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/periodictransaction/deposit')]
class PeriodicDepositTransactionController extends AbstractController
{
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;
    private PeriodicDepositTransactionService $periodicDepositTransactionService;

    public function __construct(HouseholdRepository $householdRepository, RequestStack $requestStack,
                                PeriodicDepositTransactionService $periodicDepositTransactionService)
    {
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
        $this->periodicDepositTransactionService = $periodicDepositTransactionService;
    }

    #[Route('/', name: 'housekeepingbook_periodic_deposit_transaction_index', methods: ['GET'])]
    public function index(): Response
    {
        $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->render('housekeepingbook/periodictransaction/deposit/index.html.twig', [
            'pageTitle' => t('Periodic Deposit Transactions'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'housekeepingbook_periodic_deposit_transaction_datatables', methods: ['GET'])]
    public function getPeriodicDepositTransactionsAsDatatables(Request $request): Response
    {
        $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->json(
            $this->periodicDepositTransactionService->getPeriodicDepositTransactionsAsDatatablesArray($request, $currentHousehold)
        );
    }

    #[Route('/new', name: 'housekeepingbook_periodic_deposit_transaction_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        HouseholdRepository $householdRepository,
        HouseholdUserRepository $householdUserRepository,
        RevenueAccountRepository $revenueAccountRepository
    ): Response
    {
        $household = null;
        $householdUser = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $household = $householdRepository->find($this->requestStack->getSession()->get('current_household'));
            $householdUser = $householdUserRepository->findOneByUserAndHousehold($this->getUser(), $household);
        }

        $this->denyAccessUnlessGranted('createPeriodicDepositTransaction', $household);

        $periodicDepositTransaction = new PeriodicDepositTransaction();
        $createPeriodicDepositTransaction = new PeriodicDepositTransactionDTO();

        $form = $this->createForm(PeriodicDepositTransactionType::class, $createPeriodicDepositTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->getDoctrine()->getManager();

                // Find or create the required revenue account
                $revenueAccount = $revenueAccountRepository->findOneByHouseholdAndAccountHolder($household, $createPeriodicDepositTransaction->getSource());

                if(!$revenueAccount) {
                    $revenueAccount = new RevenueAccount();
                    $revenueAccount->setHousehold($household);
                    $revenueAccount->setInitialBalance(0);
                    $revenueAccount->setInitialBalanceDate((new DateTime())->modify('midnight'));
                    $revenueAccount->setAccountHolder($createPeriodicDepositTransaction->getSource());

                    $entityManager->persist($revenueAccount);
                }

                // explicitly setting to "midnight" might not be necessary for a db date field
                $periodicDepositTransaction->setStartDate($createPeriodicDepositTransaction->getStartDate()->modify('midnight'));
                $periodicDepositTransaction->setEndDate($createPeriodicDepositTransaction->getEndDate()?->modify('midnight'));
                $periodicDepositTransaction->setBookingDayOfMonth($createPeriodicDepositTransaction->getBookingDayOfMonth());
                $periodicDepositTransaction->setBookingCategory($createPeriodicDepositTransaction->getBookingCategory());
                $periodicDepositTransaction->setSource($revenueAccount);
                $periodicDepositTransaction->setDestination($createPeriodicDepositTransaction->getDestination());
                $periodicDepositTransaction->setAmount($createPeriodicDepositTransaction->getAmount());
                $periodicDepositTransaction->setBookingInterval($createPeriodicDepositTransaction->getBookingInterval());
                $periodicDepositTransaction->setDescription($createPeriodicDepositTransaction->getDescription());
                $periodicDepositTransaction->setPrivate($createPeriodicDepositTransaction->getPrivate());
                $periodicDepositTransaction->setBookingPeriodOffset($createPeriodicDepositTransaction->getBookingPeriodOffset());

                // TODO: Do we need to explicitly check that these values are set and not null?
                $periodicDepositTransaction->setHousehold($household);
                $periodicDepositTransaction->setHouseholdUser($householdUser);

                $entityManager->persist($periodicDepositTransaction);
                $entityManager->flush();

                $this->addFlash('success', t('Periodic deposit transaction was added.'));

                return $this->redirectToRoute('housekeepingbook_periodic_deposit_transaction_new');
            } catch (Exception $exception) {
                $this->addFlash('error', t('Periodic deposit transaction could not be created: ' . $exception->getMessage()));
            }
        }

        return $this->render('housekeepingbook/periodictransaction/deposit/form.html.twig', [
            'pageTitle' => t('Add periodic deposit transaction'),
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}/edit', name: 'housekeepingbook_periodic_deposit_transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PeriodicDepositTransaction $periodicDepositTransaction, RevenueAccountRepository $revenueAccountRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $periodicDepositTransaction);

        $editPeriodicDepositTransaction = new PeriodicDepositTransactionDTO();
        $editPeriodicDepositTransaction->setStartDate($periodicDepositTransaction->getStartDate()->modify('midnight'));
        $editPeriodicDepositTransaction->setEndDate($periodicDepositTransaction->getEndDate()?->modify('midnight'));
        $editPeriodicDepositTransaction->setBookingDayOfMonth($periodicDepositTransaction->getBookingDayOfMonth());
        $editPeriodicDepositTransaction->setBookingCategory($periodicDepositTransaction->getBookingCategory());
        $editPeriodicDepositTransaction->setSource($periodicDepositTransaction->getSource()->getAccountHolder());
        $editPeriodicDepositTransaction->setDestination($periodicDepositTransaction->getDestination());
        $editPeriodicDepositTransaction->setAmount($periodicDepositTransaction->getAmount());
        $editPeriodicDepositTransaction->setBookingInterval($periodicDepositTransaction->getBookingInterval());
        $editPeriodicDepositTransaction->setDescription($periodicDepositTransaction->getDescription());
        $editPeriodicDepositTransaction->setPrivate($periodicDepositTransaction->getPrivate());
        $editPeriodicDepositTransaction->setBookingPeriodOffset($periodicDepositTransaction->getBookingPeriodOffset());

        $form = $this->createForm(PeriodicDepositTransactionType::class, $editPeriodicDepositTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->getDoctrine()->getManager();

                // Find or create the required revenue account
                $revenueAccount = $revenueAccountRepository->findOneByHouseholdAndAccountHolder($periodicDepositTransaction->getHousehold(), $editPeriodicDepositTransaction->getSource());

                if(!$revenueAccount) {
                    $revenueAccount = new RevenueAccount();
                    $revenueAccount->setHousehold($periodicDepositTransaction->getHousehold());
                    $revenueAccount->setInitialBalance(0);
                    $revenueAccount->setInitialBalanceDate((new DateTime())->modify('midnight'));
                    $revenueAccount->setAccountHolder($editPeriodicDepositTransaction->getSource());

                    $entityManager->persist($revenueAccount);
                }

                $periodicDepositTransaction->setStartDate($editPeriodicDepositTransaction->getStartDate());
                $periodicDepositTransaction->setEndDate($editPeriodicDepositTransaction->getEndDate());
                $periodicDepositTransaction->setBookingDayOfMonth($editPeriodicDepositTransaction->getBookingDayOfMonth());
                $periodicDepositTransaction->setBookingCategory($editPeriodicDepositTransaction->getBookingCategory());
                $periodicDepositTransaction->setSource($revenueAccount);
                $periodicDepositTransaction->setDestination($editPeriodicDepositTransaction->getDestination());
                $periodicDepositTransaction->setAmount($editPeriodicDepositTransaction->getAmount());
                $periodicDepositTransaction->setDescription($editPeriodicDepositTransaction->getDescription());
                $periodicDepositTransaction->setBookingInterval($editPeriodicDepositTransaction->getBookingInterval());
                $periodicDepositTransaction->setPrivate($editPeriodicDepositTransaction->getPrivate());
                $periodicDepositTransaction->setBookingPeriodOffset($editPeriodicDepositTransaction->getBookingPeriodOffset());

                $entityManager->flush();
                $this->addFlash('success', t('Periodic deposit transaction was updated.'));

                return $this->redirectToRoute('housekeepingbook_periodic_deposit_transaction_index');
            } catch (Exception) {
                $this->addFlash('error', t('Periodic deposit transaction could not be updated.'));
            }
        }

        return $this->render('housekeepingbook/periodictransaction/deposit/form.html.twig', [
            'pageTitle' => t('Edit periodic deposit transaction'),
            'form' => $form->createView(),
            'periodicDepositTransaction' => $periodicDepositTransaction,
            'button_label' => t('Update'),
        ]);
    }


    #[Route('/{id}', name: 'housekeepingbook_periodic_deposit_transaction_delete', methods: ['POST'])]
    public function delete(Request $request, PeriodicDepositTransaction $periodicDepositTransaction): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_periodic_deposit_transaction_' . $periodicDepositTransaction->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete', $periodicDepositTransaction);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($periodicDepositTransaction);
                $entityManager->flush();
                $this->addFlash('success', t('Periodic deposit transaction was deleted.'));
            }else {
                throw new Exception('invalid CSRF token');
            }
        }catch (Exception) {
            $this->addFlash('error', t('Periodic deposit transaction could not be deleted.'));
        }

        return $this->redirectToRoute('housekeepingbook_periodic_deposit_transaction_index');
    }
}
