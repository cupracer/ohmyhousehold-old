<?php

namespace App\Controller\Account;

use App\Entity\AssetAccount;
use App\Entity\DTO\AssetAccountDTO;
use App\Form\Account\AssetAccountType;
use App\Service\Account\AssetAccountService;
use App\Service\UserSettingsService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_HOUSEKEEPINGBOOK")]
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/account/asset')]
class AssetAccountController extends AbstractController
{
    private AssetAccountService $accountService;
    private UserSettingsService $userSettingsService;

    public function __construct(AssetAccountService $accountService, UserSettingsService $userSettingsService)
    {
        $this->accountService = $accountService;
        $this->userSettingsService = $userSettingsService;
    }

    #[Route('/', name: 'housekeepingbook_asset_account_index', methods: ['GET'])]
    public function index(): Response
    {
        $currentHousehold = $this->userSettingsService->getCurrentHousehold($this->getUser());

        return $this->render('housekeepingbook/account/asset/index.html.twig', [
            'pageTitle' => t('Asset Accounts'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'housekeepingbook_asset_account_datatables', methods: ['GET'])]
    public function getAssetAccountsAsDatatables(Request $request): Response
    {
        $currentHousehold = $this->userSettingsService->getCurrentHousehold($this->getUser());

        return $this->json(
            $this->accountService->getAssetAccountsAsDatatablesArray($request, $currentHousehold)
        );
    }


    #[Route('/new', name: 'housekeepingbook_asset_account_new', methods: ['GET', 'POST'])]
    public function newAssetAccount(
        Request $request
    ): Response
    {
        $currentHousehold = $this->userSettingsService->getCurrentHousehold($this->getUser());

        $this->denyAccessUnlessGranted('createAssetAccount', $currentHousehold);

        $account = new AssetAccount();
        $createAccount = new AssetAccountDTO();
        $createAccount->setInitialBalance(0);

        $form = $this->createForm(AssetAccountType::class, $createAccount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $account->setHousehold($currentHousehold);
                $account->setName($createAccount->getName());
                $account->setAccountType($createAccount->getAccountType());
                $account->setIban($createAccount->getIban());
                $account->setInitialBalance($createAccount->getInitialBalance());

                foreach($createAccount->getOwners() as $owner) {
                    $account->addOwner($owner);
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($account);
                $entityManager->flush();

                $this->addFlash('success', t('Asset account was added.'));

                return $this->redirectToRoute('housekeepingbook_asset_account_index');
            }catch (\Exception) {
                $this->addFlash('error', t('Asset account could not be created.'));
            }
        }

        return $this->render('housekeepingbook/account/asset/form.html.twig', [
            'pageTitle' => t('Add asset account'),
            'form' => $form->createView(),
        ]);
    }



    #[Route('/{id}/edit', name: 'housekeepingbook_asset_account_edit', methods: ['GET', 'POST'])]
    public function editAssetAccount(Request $request, AssetAccount $assetAccount): Response
    {
        $this->denyAccessUnlessGranted('edit', $assetAccount);

        $editAssetAccount = new AssetAccountDTO();
        $editAssetAccount->setName($assetAccount->getName());
        $editAssetAccount->setAccountType($assetAccount->getAccountType());
        $editAssetAccount->setIban($assetAccount->getIban());
        $editAssetAccount->setInitialBalance($assetAccount->getInitialBalance());
        $editAssetAccount->setOwners($assetAccount->getOwners());

        $form = $this->createForm(AssetAccountType::class, $editAssetAccount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->getDoctrine()->getManager();

                $assetAccount->setName($editAssetAccount->getName());
                $assetAccount->setAccountType($editAssetAccount->getAccountType());
                $assetAccount->setIban($editAssetAccount->getIban());
                $assetAccount->setInitialBalance($editAssetAccount->getInitialBalance());

                foreach($editAssetAccount->getOwners() as $owner) {
                    $assetAccount->addOwner($owner);
                }

                $entityManager->flush();
                $this->addFlash('success', t('Asset account was updated.'));

                return $this->redirectToRoute('housekeepingbook_asset_account_index');
            } catch (\Exception) {
                $this->addFlash('error', t('Asset account could not be updated.'));
            }
        }

        return $this->render('housekeepingbook/account/asset/form.html.twig', [
            'pageTitle' => t('Edit asset account'),
            'form' => $form->createView(),
            'assetAccount' => $assetAccount,
            'button_label' => t('Update'),
        ]);
    }

    #[Route('/{id}', name: 'housekeepingbook_asset_account_delete', methods: ['POST'])]
    public function delete(Request $request, AssetAccount $assetAccount): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_asset_account_' . $assetAccount->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete', $assetAccount);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($assetAccount);
                $entityManager->flush();
                $this->addFlash('success', t('Asset account was deleted.'));
            }else {
                throw new \Exception('invalid CSRF token');
            }
        }catch (\Exception) {
            $this->addFlash('error', t('Asset account could not be deleted.'));
        }

        return $this->redirectToRoute('housekeepingbook_asset_account_index');
    }
}
