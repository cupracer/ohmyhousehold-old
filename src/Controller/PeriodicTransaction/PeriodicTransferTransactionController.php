<?php

namespace App\Controller\PeriodicTransaction;

use App\Entity\DTO\PeriodicTransferTransactionDTO;
use App\Entity\PeriodicTransferTransaction;
use App\Form\PeriodicTransaction\PeriodicTransferTransactionType;
use App\Repository\HouseholdRepository;
use App\Repository\HouseholdUserRepository;
use App\Service\PeriodicTransaction\PeriodicTransferTransactionService;
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
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/periodictransaction/transfer')]
class PeriodicTransferTransactionController extends AbstractController
{
    private ManagerRegistry $managerRegistry;
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;
    private PeriodicTransferTransactionService $periodicTransferTransactionService;

    public function __construct(HouseholdRepository $householdRepository, RequestStack $requestStack,
                                PeriodicTransferTransactionService $periodicTransferTransactionService,
                                ManagerRegistry $managerRegistry)
    {
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
        $this->periodicTransferTransactionService = $periodicTransferTransactionService;
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/', name: 'housekeepingbook_periodic_transfer_transaction_index', methods: ['GET'])]
    public function index(): Response
    {
        $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->render('housekeepingbook/periodictransaction/transfer/index.html.twig', [
            'pageTitle' => t('Periodic Transfer Transactions'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'housekeepingbook_periodic_transfer_transaction_datatables', methods: ['GET'])]
    public function getPeriodicTransferTransactionsAsDatatables(Request $request): Response
    {
        $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->json(
            $this->periodicTransferTransactionService->getPeriodicTransferTransactionsAsDatatablesArray($request, $currentHousehold)
        );
    }

    #[Route('/new', name: 'housekeepingbook_periodic_transfer_transaction_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        HouseholdRepository $householdRepository,
        HouseholdUserRepository $householdUserRepository
    ): Response
    {
        $household = null;
        $householdUser = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $household = $householdRepository->find($this->requestStack->getSession()->get('current_household'));
            $householdUser = $householdUserRepository->findOneByUserAndHousehold($this->getUser(), $household);
        }

        $this->denyAccessUnlessGranted('createPeriodicTransferTransaction', $household);

        $periodicTransferTransaction = new PeriodicTransferTransaction();
        $createPeriodicTransferTransaction = new PeriodicTransferTransactionDTO();

        $form = $this->createForm(PeriodicTransferTransactionType::class, $createPeriodicTransferTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->managerRegistry->getManager();

                // explicitly setting to "midnight" might not be necessary for a db date field
                $periodicTransferTransaction->setStartDate($createPeriodicTransferTransaction->getStartDate());
                $periodicTransferTransaction->setEndDate($createPeriodicTransferTransaction->getEndDate());
                $periodicTransferTransaction->setBookingDayOfMonth($createPeriodicTransferTransaction->getBookingDayOfMonth());
                $periodicTransferTransaction->setSource($createPeriodicTransferTransaction->getSource());
                $periodicTransferTransaction->setDestination($createPeriodicTransferTransaction->getDestination());
                $periodicTransferTransaction->setAmount($createPeriodicTransferTransaction->getAmount());
                $periodicTransferTransaction->setBookingInterval($createPeriodicTransferTransaction->getBookingInterval());
                $periodicTransferTransaction->setDescription($createPeriodicTransferTransaction->getDescription());
                $periodicTransferTransaction->setPrivate($createPeriodicTransferTransaction->getPrivate());
                $periodicTransferTransaction->setBookingPeriodOffset($createPeriodicTransferTransaction->getBookingPeriodOffset());

                // TODO: Do we need to explicitly check that these values are set and not null?
                $periodicTransferTransaction->setHousehold($household);
                $periodicTransferTransaction->setHouseholdUser($householdUser);

                $entityManager->persist($periodicTransferTransaction);
                $entityManager->flush();

                $this->addFlash('success', t('Periodic transfer transaction was added.'));

                return $this->redirectToRoute('housekeepingbook_periodic_transfer_transaction_new');
            } catch (Exception $exception) {
                $this->addFlash('error', t('Periodic transfer transaction could not be created: ' . $exception->getMessage()));
            }
        }

        return $this->render('housekeepingbook/periodictransaction/transfer/form.html.twig', [
            'pageTitle' => t('Add periodic transfer transaction'),
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}/edit', name: 'housekeepingbook_periodic_transfer_transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PeriodicTransferTransaction $periodicTransferTransaction): Response
    {
        $this->denyAccessUnlessGranted('edit', $periodicTransferTransaction);

        $editPeriodicTransferTransaction = new PeriodicTransferTransactionDTO();
        $editPeriodicTransferTransaction->setStartDate($periodicTransferTransaction->getStartDate());
        $editPeriodicTransferTransaction->setEndDate($periodicTransferTransaction->getEndDate());
        $editPeriodicTransferTransaction->setBookingDayOfMonth($periodicTransferTransaction->getBookingDayOfMonth());
        $editPeriodicTransferTransaction->setSource($periodicTransferTransaction->getSource());
        $editPeriodicTransferTransaction->setDestination($periodicTransferTransaction->getDestination());
        $editPeriodicTransferTransaction->setAmount($periodicTransferTransaction->getAmount());
        $editPeriodicTransferTransaction->setBookingInterval($periodicTransferTransaction->getBookingInterval());
        $editPeriodicTransferTransaction->setDescription($periodicTransferTransaction->getDescription());
        $editPeriodicTransferTransaction->setPrivate($periodicTransferTransaction->getPrivate());
        $editPeriodicTransferTransaction->setBookingPeriodOffset($periodicTransferTransaction->getBookingPeriodOffset());

        $form = $this->createForm(PeriodicTransferTransactionType::class, $editPeriodicTransferTransaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->managerRegistry->getManager();

                $periodicTransferTransaction->setStartDate($editPeriodicTransferTransaction->getStartDate());
                $periodicTransferTransaction->setEndDate($editPeriodicTransferTransaction->getEndDate());
                $periodicTransferTransaction->setBookingDayOfMonth($editPeriodicTransferTransaction->getBookingDayOfMonth());
                $periodicTransferTransaction->setSource($editPeriodicTransferTransaction->getSource());
                $periodicTransferTransaction->setDestination($editPeriodicTransferTransaction->getDestination());
                $periodicTransferTransaction->setAmount($editPeriodicTransferTransaction->getAmount());
                $periodicTransferTransaction->setDescription($editPeriodicTransferTransaction->getDescription());
                $periodicTransferTransaction->setBookingInterval($editPeriodicTransferTransaction->getBookingInterval());
                $periodicTransferTransaction->setPrivate($editPeriodicTransferTransaction->getPrivate());
                $periodicTransferTransaction->setBookingPeriodOffset($editPeriodicTransferTransaction->getBookingPeriodOffset());

                $entityManager->flush();
                $this->addFlash('success', t('Periodic transfer transaction was updated.'));

                return $this->redirectToRoute('housekeepingbook_periodic_transfer_transaction_index');
            } catch (Exception) {
                $this->addFlash('error', t('Periodic transfer transaction could not be updated.'));
            }
        }

        return $this->render('housekeepingbook/periodictransaction/transfer/form.html.twig', [
            'pageTitle' => t('Edit periodic transfer transaction'),
            'form' => $form->createView(),
            'periodicTransferTransaction' => $periodicTransferTransaction,
            'button_label' => t('Update'),
        ]);
    }


    #[Route('/{id}', name: 'housekeepingbook_periodic_transfer_transaction_delete', methods: ['POST'])]
    public function delete(Request $request, PeriodicTransferTransaction $periodicTransferTransaction): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_periodic_transfer_transaction_' . $periodicTransferTransaction->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete', $periodicTransferTransaction);
                $entityManager = $this->managerRegistry->getManager();
                $entityManager->remove($periodicTransferTransaction);
                $entityManager->flush();
                $this->addFlash('success', t('Periodic transfer transaction was deleted.'));
            }else {
                throw new Exception('invalid CSRF token');
            }
        }catch (Exception) {
            $this->addFlash('error', t('Periodic transfer transaction could not be deleted.'));
        }

        return $this->redirectToRoute('housekeepingbook_periodic_transfer_transaction_index');
    }
}
