<?php

namespace App\Controller\Transaction;

use App\Entity\DTO\TransferTransactionDTO;
use App\Entity\TransferTransaction;
use App\Form\Transaction\TransferTransactionType;
use App\Repository\HouseholdRepository;
use App\Repository\HouseholdUserRepository;
use App\Service\Transaction\TransferTransactionService;
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
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/transaction/transfer')]
class TransferTransactionController extends AbstractController
{
    private ManagerRegistry $managerRegistry;
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;
    private TransferTransactionService $transferTransactionService;

    public function __construct(HouseholdRepository        $householdRepository, RequestStack $requestStack,
                                TransferTransactionService $transferTransactionService, ManagerRegistry $managerRegistry)
    {
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
        $this->transferTransactionService = $transferTransactionService;
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/', name: 'housekeepingbook_transfer_transaction_index', methods: ['GET'])]
    public function index(): Response
    {
        $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->render('housekeepingbook/transaction/transfer/index.html.twig', [
            'pageTitle' => t('Transfer Transactions'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'housekeepingbook_transfer_transaction_datatables', methods: ['GET'])]
    public function getTransferTransactionsAsDatatables(Request $request): Response
    {
        $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->json(
            $this->transferTransactionService->getTransferTransactionsAsDatatablesArray($request, $currentHousehold)
        );
    }


    #[Route('/new', name: 'housekeepingbook_transfer_transaction_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        HouseholdRepository $householdRepository,
        HouseholdUserRepository $householdUserRepository,
    ): Response
    {
        $household = null;
        $householdUser = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $household = $householdRepository->find($this->requestStack->getSession()->get('current_household'));
            $householdUser = $householdUserRepository->findOneByUserAndHousehold($this->getUser(), $household);
        }

        $this->denyAccessUnlessGranted('createTransferTransaction', $household);

        $transferTransaction = new TransferTransaction();
        $createTransferTransaction = new TransferTransactionDTO();

        // set initial values
        $createTransferTransaction->setBookingDate((new DateTime())->modify('midnight'));

        $form = $this->createForm(TransferTransactionType::class, $createTransferTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->managerRegistry->getManager();

                // explicitly setting to "midnight" might not be necessary for a db date field
                $transferTransaction->setBookingDate($createTransferTransaction->getBookingDate()->modify('midnight'));
                $transferTransaction->setSource($createTransferTransaction->getSource());
                $transferTransaction->setDestination($createTransferTransaction->getDestination());
                $transferTransaction->setAmount($createTransferTransaction->getAmount());
                $transferTransaction->setDescription($createTransferTransaction->getDescription());
                $transferTransaction->setPrivate($createTransferTransaction->getPrivate());
                $transferTransaction->setBookingPeriodOffset($createTransferTransaction->getBookingPeriodOffset());
                $transferTransaction->setCompleted($createTransferTransaction->isCompleted());

                // TODO: Do we need to explicitly check that these values are set and not null?
                $transferTransaction->setHousehold($household);
                $transferTransaction->setHouseholdUser($householdUser);

                $entityManager->persist($transferTransaction);
                $entityManager->flush();

                $this->addFlash('success', t('Transfer transaction was added.'));

                return $this->redirectToRoute('housekeepingbook_transfer_transaction_new');
            } catch (Exception $exception) {
                $this->addFlash('error', t('Transfer transaction could not be created: ' . $exception->getMessage()));
            }
        }

        return $this->render('housekeepingbook/transaction/transfer/form.html.twig', [
            'pageTitle' => t('Add transfer transaction'),
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}/edit', name: 'housekeepingbook_transfer_transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TransferTransaction $transferTransaction): Response
    {
        $this->denyAccessUnlessGranted('edit', $transferTransaction);

        $editTransferTransaction = new TransferTransactionDTO();
        $editTransferTransaction->setBookingDate($transferTransaction->getBookingDate());
        $editTransferTransaction->setSource($transferTransaction->getSource());
        $editTransferTransaction->setDestination($transferTransaction->getDestination());
        $editTransferTransaction->setAmount($transferTransaction->getAmount());
        $editTransferTransaction->setDescription($transferTransaction->getDescription());
        $editTransferTransaction->setPrivate($transferTransaction->getPrivate());
        $editTransferTransaction->setBookingPeriodOffset($transferTransaction->getBookingPeriodOffset());
        $editTransferTransaction->setCompleted($transferTransaction->isCompleted());

        $form = $this->createForm(TransferTransactionType::class, $editTransferTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->managerRegistry->getManager();

                $transferTransaction->setBookingDate($editTransferTransaction->getBookingDate());
                $transferTransaction->setSource($editTransferTransaction->getSource());
                $transferTransaction->setDestination($editTransferTransaction->getDestination());
                $transferTransaction->setAmount($editTransferTransaction->getAmount());
                $transferTransaction->setDescription($editTransferTransaction->getDescription());
                $transferTransaction->setPrivate($editTransferTransaction->getPrivate());
                $transferTransaction->setBookingPeriodOffset($editTransferTransaction->getBookingPeriodOffset());
                $transferTransaction->setCompleted($editTransferTransaction->isCompleted());

                $entityManager->flush();
                $this->addFlash('success', t('Transfer transaction was updated.'));

                return $this->redirectToRoute('housekeepingbook_transfer_transaction_index');
            } catch (Exception) {
                $this->addFlash('error', t('Transfer transaction could not be updated.'));
            }
        }

        return $this->render('housekeepingbook/transaction/transfer/form.html.twig', [
            'pageTitle' => t('Edit transfer transaction'),
            'form' => $form->createView(),
            'transferTransaction' => $transferTransaction,
            'button_label' => t('Update'),
        ]);
    }


    #[Route('/{id}', name: 'housekeepingbook_transfer_transaction_delete', methods: ['POST'])]
    public function delete(Request $request, TransferTransaction $transferTransaction): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_transfer_transaction_' . $transferTransaction->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete', $transferTransaction);
                $entityManager = $this->managerRegistry->getManager();
                $entityManager->remove($transferTransaction);
                $entityManager->flush();
                $this->addFlash('success', t('Transfer transaction was deleted.'));
            }else {
                throw new Exception('invalid CSRF token');
            }
        }catch (Exception) {
            $this->addFlash('error', t('Transfer transaction could not be deleted.'));
        }

        return $this->redirectToRoute('housekeepingbook_transfer_transaction_index');
    }

    #[Route('/{id}/edit/state', name: 'housekeepingbook_transfer_transaction_edit_state', methods: ['POST'])]
    public function editState(Request $request, TransferTransaction $transferTransaction): Response
    {
        $this->denyAccessUnlessGranted('edit', $transferTransaction);

        $state = $request->request->get('state') === 'true';

        try {
            $transferTransaction->setCompleted($state);

            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($transferTransaction);
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
