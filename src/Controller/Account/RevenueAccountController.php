<?php

namespace App\Controller\Account;

use App\Service\Account\RevenueAccountService;
use App\Service\UserSettingsService;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_HOUSEKEEPINGBOOK")]
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/account/revenue')]
class RevenueAccountController extends AbstractController
{
    private ManagerRegistry $managerRegistry;
    private RevenueAccountService $accountService;
    private UserSettingsService $userSettingsService;

    public function __construct(RevenueAccountService $accountService, UserSettingsService $userSettingsService, ManagerRegistry $managerRegistry)
    {
        $this->accountService = $accountService;
        $this->userSettingsService = $userSettingsService;
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/', name: 'housekeepingbook_revenue_account_index', methods: ['GET'])]
    public function index(): Response
    {
        $currentHousehold = $this->userSettingsService->getCurrentHousehold($this->getUser());

        return $this->render('housekeepingbook/account/revenue/index.html.twig', [
            'pageTitle' => t('Revenue Accounts'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'housekeepingbook_revenue_account_datatables', methods: ['GET'])]
    public function getAssetAccountsAsDatatables(Request $request): Response
    {
        $currentHousehold = $this->userSettingsService->getCurrentHousehold($this->getUser());

        return $this->json(
            $this->accountService->getRevenueAccountsAsDatatablesArray($request, $currentHousehold)
        );
    }


//    #[Route('/new', name: 'housekeepingbook_revenue_account_new', methods: ['GET', 'POST'])]
//    public function newAssetAccount(
//        Request $request,
//        SessionInterface $session,
//        HouseholdRepository $householdRepository
//    ): Response
//    {
//        $currentHousehold = $this->userSettingsService->getCurrentHousehold($this->getUser());
//
//        $this->denyAccessUnlessGranted('createAssetAccount', $currentHousehold);
//
//        $account = new AssetAccount();
//        $createAccount = new AccountHolderDTO();
//
//        $form = $this->createForm(AccountHolderType::class, $createAccountHolder);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            try {
//                $accountHolder->setName($createAccountHolder->getName());
//                $accountHolder->setHousehold($currentHousehold);
//
//                $entityManager = $this->managerRegistry->getManager();
//                $entityManager->persist($accountHolder);
//                $entityManager->flush();
//
//                $this->addFlash('success', t('Account holder was added.'));
//
//                return $this->redirectToRoute('housekeepingbook_accountholder_index');
//            }catch (\Exception) {
//                $this->addFlash('error', t('Account holder could not be created.'));
//            }
//        }
//
//        return $this->render('housekeepingbook/accountholder/form.html.twig', [
//            'pageTitle' => t('Add account holder'),
//            'form' => $form->createView(),
//        ]);
//    }



//    #[Route('/{id}/edit', name: 'housekeepingbook_revenue_account_edit', methods: ['GET', 'POST'])]
//    public function editAssetAccount(Request $request, AccountHolder $accountHolder): Response
//    {
//        return $this->redirectToRoute('housekeepingbook_revenue_account_index');
//    }
}
