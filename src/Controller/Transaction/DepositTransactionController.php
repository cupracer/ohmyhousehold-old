<?php

namespace App\Controller\Transaction;

use App\Entity\DepositTransaction;
use App\Entity\DTO\DepositTransactionDTO;
use App\Entity\RevenueAccount;
use App\Form\Transaction\DepositTransactionType;
use App\Repository\Account\RevenueAccountRepository;
use App\Repository\HouseholdRepository;
use App\Repository\HouseholdUserRepository;
use App\Service\Transaction\DepositTransactionService;
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
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/transaction/deposit')]
class DepositTransactionController extends AbstractController
{
    private ManagerRegistry $managerRegistry;
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;
    private DepositTransactionService $depositTransactionService;

    public function __construct(HouseholdRepository       $householdRepository, RequestStack $requestStack,
                                DepositTransactionService $depositTransactionService, ManagerRegistry $managerRegistry)
    {
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
        $this->depositTransactionService = $depositTransactionService;
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/', name: 'housekeepingbook_deposit_transaction_index', methods: ['GET'])]
    public function index(): Response
    {
        $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->render('housekeepingbook/transaction/deposit/index.html.twig', [
            'pageTitle' => t('deposits'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'housekeepingbook_deposit_transaction_datatables', methods: ['GET'])]
    public function getDepositTransactionsAsDatatables(Request $request): Response
    {
        $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->json(
            $this->depositTransactionService->getDepositTransactionsAsDatatablesArray($request, $currentHousehold)
        );
    }

    #[Route('/new', name: 'housekeepingbook_deposit_transaction_new', methods: ['GET', 'POST'])]
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

        $this->denyAccessUnlessGranted('createDepositTransaction', $household);

        $depositTransaction = new DepositTransaction();
        $createDepositTransaction = new DepositTransactionDTO();

        // set initial values
        $createDepositTransaction->setBookingDate((new DateTime())->modify('midnight'));

        $form = $this->createForm(DepositTransactionType::class, $createDepositTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->managerRegistry->getManager();

                // Find or create the required revenue account
                $revenueAccount = $revenueAccountRepository->findOneByHouseholdAndAccountHolder($household, $createDepositTransaction->getSource());

                if(!$revenueAccount) {
                    $revenueAccount = new RevenueAccount();
                    $revenueAccount->setHousehold($household);
                    $revenueAccount->setInitialBalance(0);
                    $revenueAccount->setInitialBalanceDate((new DateTime())->modify('midnight'));
                    $revenueAccount->setAccountHolder($createDepositTransaction->getSource());

                    $entityManager->persist($revenueAccount);
                }

                // explicitly setting to "midnight" might not be necessary for a db date field
                $depositTransaction->setBookingDate($createDepositTransaction->getBookingDate()->modify('midnight'));
                $depositTransaction->setBookingCategory($createDepositTransaction->getBookingCategory());
                $depositTransaction->setSource($revenueAccount);
                $depositTransaction->setDestination($createDepositTransaction->getDestination());
                $depositTransaction->setAmount($createDepositTransaction->getAmount());
                $depositTransaction->setDescription($createDepositTransaction->getDescription());
                $depositTransaction->setPrivate($createDepositTransaction->getPrivate());
                $depositTransaction->setBookingPeriodOffset($createDepositTransaction->getBookingPeriodOffset());
                $depositTransaction->setCompleted($createDepositTransaction->isCompleted());

                // TODO: Do we need to explicitly check that these values are set and not null?
                $depositTransaction->setHousehold($household);
                $depositTransaction->setHouseholdUser($householdUser);

                $entityManager->persist($depositTransaction);
                $entityManager->flush();

                $this->addFlash('success', t('Deposit transaction was added.'));

                return $this->redirectToRoute('housekeepingbook_deposit_transaction_new');
            } catch (Exception $exception) {
                $this->addFlash('error', t('Deposit transaction could not be created: ' . $exception->getMessage()));
            }
        }

        return $this->render('housekeepingbook/transaction/deposit/form.html.twig', [
            'pageTitle' => t('add_deposit_transaction'),
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}/edit', name: 'housekeepingbook_deposit_transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DepositTransaction $depositTransaction, RevenueAccountRepository $revenueAccountRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $depositTransaction);

        $editDepositTransaction = new DepositTransactionDTO();
        $editDepositTransaction->setBookingDate($depositTransaction->getBookingDate());
        $editDepositTransaction->setBookingCategory($depositTransaction->getBookingCategory());
        $editDepositTransaction->setSource($depositTransaction->getSource()->getAccountHolder());
        $editDepositTransaction->setDestination($depositTransaction->getDestination());
        $editDepositTransaction->setAmount($depositTransaction->getAmount());
        $editDepositTransaction->setDescription($depositTransaction->getDescription());
        $editDepositTransaction->setPrivate($depositTransaction->getPrivate());
        $editDepositTransaction->setBookingPeriodOffset($depositTransaction->getBookingPeriodOffset());
        $editDepositTransaction->setCompleted($depositTransaction->isCompleted());

        $form = $this->createForm(DepositTransactionType::class, $editDepositTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->managerRegistry->getManager();

                // Find or create the required revenue account
                $revenueAccount = $revenueAccountRepository->findOneByHouseholdAndAccountHolder($depositTransaction->getHousehold(), $editDepositTransaction->getSource());

                if(!$revenueAccount) {
                    $revenueAccount = new RevenueAccount();
                    $revenueAccount->setHousehold($depositTransaction->getHousehold());
                    $revenueAccount->setInitialBalance(0);
                    $revenueAccount->setInitialBalanceDate((new DateTime())->modify('midnight'));
                    $revenueAccount->setAccountHolder($editDepositTransaction->getSource());

                    $entityManager->persist($revenueAccount);
                }

                $depositTransaction->setBookingDate($editDepositTransaction->getBookingDate());
                $depositTransaction->setBookingCategory($editDepositTransaction->getBookingCategory());
                $depositTransaction->setSource($revenueAccount);
                $depositTransaction->setDestination($editDepositTransaction->getDestination());
                $depositTransaction->setAmount($editDepositTransaction->getAmount());
                $depositTransaction->setDescription($editDepositTransaction->getDescription());
                $depositTransaction->setPrivate($editDepositTransaction->getPrivate());
                $depositTransaction->setBookingPeriodOffset($editDepositTransaction->getBookingPeriodOffset());
                $depositTransaction->setCompleted($editDepositTransaction->isCompleted());

                $entityManager->flush();
                $this->addFlash('success', t('Deposit transaction was updated.'));

                return $this->redirectToRoute('housekeepingbook_deposit_transaction_index');
            } catch (Exception) {
                $this->addFlash('error', t('Deposit transaction could not be updated.'));
            }
        }

        return $this->render('housekeepingbook/transaction/deposit/form.html.twig', [
            'pageTitle' => t('edit_deposit_transaction'),
            'form' => $form->createView(),
            'depositTransaction' => $depositTransaction,
            'button_label' => t('update.button'),
        ]);
    }


    #[Route('/{id}', name: 'housekeepingbook_deposit_transaction_delete', methods: ['POST'])]
    public function delete(Request $request, DepositTransaction $depositTransaction): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_deposit_transaction_' . $depositTransaction->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete', $depositTransaction);
                $entityManager = $this->managerRegistry->getManager();
                $entityManager->remove($depositTransaction);
                $entityManager->flush();
                $this->addFlash('success', t('Deposit transaction was deleted.'));
            }else {
                throw new Exception('invalid CSRF token');
            }
        }catch (Exception) {
            $this->addFlash('error', t('Deposit transaction could not be deleted.'));
        }

        return $this->redirectToRoute('housekeepingbook_deposit_transaction_index');
    }

    #[Route('/{id}/edit/state', name: 'housekeepingbook_deposit_transaction_edit_state', methods: ['POST'])]
    public function editState(Request $request, DepositTransaction $depositTransaction): Response
    {
        $this->denyAccessUnlessGranted('edit', $depositTransaction);

        $state = $request->request->get('state') === 'true';

        try {
            $depositTransaction->setCompleted($state);

            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($depositTransaction);
            $entityManager->flush();

            $transactionStateStr = $state ? 'completed' : "unconfirmed";

            $this->addFlash('success', t("Transaction state has been marked as " . $transactionStateStr . "."));
            return $this->json([
                'success' => true,
            ]);
        }catch (Exception) {
            $this->addFlash('error', t("Failed to mark transaction state as " . $transactionStateStr . "."));
            return $this->json([
                'success' => false,
            ]);
        }
    }
}
