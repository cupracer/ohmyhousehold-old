<?php

namespace App\Controller;

use App\Entity\AccountHolder;
use App\Entity\DTO\AccountHolderDTO;
use App\Form\AccountHolderType;
use App\Repository\AccountHolderRepository;
use App\Repository\HouseholdRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_HOUSEKEEPINGBOOK")]
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/accountholder')]
class AccountHolderController extends AbstractController
{
    #[Route('/', name: 'housekeepingbook_accountholder_index', methods: ['GET'])]
    public function index(AccountHolderRepository $accountHolderRepository, HouseholdRepository $householdRepository, SessionInterface $session): Response
    {
        $currentHousehold = $householdRepository->find($session->get('current_household'));

        return $this->render('housekeepingbook/accountholder/index.html.twig', [
            'pageTitle' => t('Account holders'),
            'household' => $currentHousehold,
            'accountHolders' => $accountHolderRepository->findAllGrantedByHousehold($currentHousehold),
        ]);
    }

    #[Route('/new', name: 'housekeepingbook_accountholder_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        SessionInterface $session,
        HouseholdRepository $householdRepository
    ): Response
    {
        $household = null;

        if($session->has('current_household')) {
            $household = $householdRepository->find($session->get('current_household'));
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

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($accountHolder);
                $entityManager->flush();

                $this->addFlash('success', t('Account holder was added.'));

                return $this->redirectToRoute('housekeepingbook_accountholder_index');
            }catch (\Exception) {
                $this->addFlash('error', t('Account holder could not be created.'));
            }
        }

        return $this->render('housekeepingbook/accountholder/form.html.twig', [
            'pageTitle' => t('Add account holder'),
            'accountHolder' => $accountHolder,
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

                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', t('Account holder was updated.'));

                return $this->redirectToRoute('housekeepingbook_accountholder_index');
            }catch(\Exception) {
                $this->addFlash('error', t('Account holder could not be updated.'));
            }
        }

        return $this->render('housekeepingbook/accountholder/form.html.twig', [
            'pageTitle' => t('Edit account holder'),
            'accountHolder' => $accountHolder,
            'form' => $form->createView(),
            'button_label' => t('Update'),
        ]);
    }

    #[Route('/{id}', name: 'housekeepingbook_accountholder_delete', methods: ['POST'])]
    public function delete(Request $request, AccountHolder $accountHolder): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete' . $accountHolder->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete', $accountHolder);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($accountHolder);
                $entityManager->flush();
                $this->addFlash('success', t('Account holder was deleted.'));
            }else {
                throw new \Exception('invalid CSRF token');
            }
        }catch (\Exception) {
            $this->addFlash('error', t('Account holder could not be deleted.'));
        }

        return $this->redirectToRoute('housekeepingbook_accountholder_index');
    }
}
