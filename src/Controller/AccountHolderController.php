<?php

namespace App\Controller;

use App\Entity\AccountHolder;
use App\Entity\DTO\AccountHolderDTO;
use App\Form\AccountHolderType;
use App\Repository\HouseholdRepository;
use App\Service\AccountHolderService;
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
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/accountholder')]
class AccountHolderController extends AbstractController
{
    private ManagerRegistry $managerRegistry;
    private RequestStack $requestStack;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack)
    {
        $this->managerRegistry = $managerRegistry;
        $this->requestStack = $requestStack;
    }

    #[Route('/', name: 'housekeepingbook_accountholder_index', methods: ['GET'])]
    public function index(HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->render('housekeepingbook/accountholder/index.html.twig', [
            'pageTitle' => t('Account holders'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'housekeepingbook_accountholder_datatables', methods: ['GET'])]
    public function getAsDatatables(Request $request, AccountHolderService $accountHolderService, HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->json(
            $accountHolderService->getAccountHoldersAsDatatablesArray($request, $currentHousehold)
        );
    }

    #[Route('/select2', name: 'housekeepingbook_accountholder_select2', methods: ['GET'])]
    public function getAsSelect2(Request $request, AccountHolderService $accountHolderService, HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->json(
            $accountHolderService->getAccountHoldersAsSelect2Array($request, $currentHousehold)
        );
    }

    #[Route('/new', name: 'housekeepingbook_accountholder_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        HouseholdRepository $householdRepository
    ): Response
    {
        $household = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $household = $householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        $this->denyAccessUnlessGranted('createAccountHolder', $household);

        $accountHolder = new AccountHolder();
        $createAccountHolder = new AccountHolderDTO();

        $form = $this->createForm(AccountHolderType::class, $createAccountHolder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $accountHolder->setName($createAccountHolder->getName());
                $accountHolder->setHousehold($household);

                $entityManager = $this->managerRegistry->getManager();
                $entityManager->persist($accountHolder);
                $entityManager->flush();

                $this->addFlash('success', t('Account holder was added.'));

                return $this->redirectToRoute('housekeepingbook_accountholder_index');
            }catch (Exception) {
                $this->addFlash('error', t('Account holder could not be created.'));
            }
        }

        return $this->render('housekeepingbook/accountholder/form.html.twig', [
            'pageTitle' => t('Add account holder'),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'housekeepingbook_accountholder_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AccountHolder $accountHolder): Response
    {
        $this->denyAccessUnlessGranted('edit', $accountHolder);

        $editAccountHolder = new AccountHolderDTO();
        $editAccountHolder->setName($accountHolder->getName());

        $form = $this->createForm(AccountHolderType::class, $editAccountHolder, ['accountHolder' => $accountHolder]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $accountHolder->setName($editAccountHolder->getName());

                $this->managerRegistry->getManager()->flush();

                $this->addFlash('success', t('Account holder was updated.'));

                return $this->redirectToRoute('housekeepingbook_accountholder_index');
            }catch(Exception) {
                $this->addFlash('error', t('Account holder could not be updated.'));
            }
        }

        return $this->render('housekeepingbook/accountholder/form.html.twig', [
            'pageTitle' => t('Edit account holder'),
            'form' => $form->createView(),
            'accountHolder' => $accountHolder,
            'button_label' => t('Update'),
        ]);
    }

    #[Route('/{id}', name: 'housekeepingbook_accountholder_delete', methods: ['POST'])]
    public function delete(Request $request, AccountHolder $accountHolder): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_account_holder_' . $accountHolder->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete' , $accountHolder);
                $entityManager = $this->managerRegistry->getManager();
                $entityManager->remove($accountHolder);
                $entityManager->flush();
                $this->addFlash('success', t('Account holder was deleted.'));
            }else {
                throw new Exception('invalid CSRF token');
            }
        }catch (Exception) {
            $this->addFlash('error', t('Account holder could not be deleted.'));
        }

        return $this->redirectToRoute('housekeepingbook_accountholder_index');
    }
}
