<?php

namespace App\Controller\Supplies;

use App\Entity\Supplies\Supply;
use App\Entity\Supplies\DTO\SupplyDTO;
use App\Form\Supplies\SupplyType;
use App\Repository\HouseholdRepository;
use App\Service\Supplies\SupplyService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_SUPPLIES")]
#[Route('/{_locale<%app.supported_locales%>}/supplies/supply')]
class SupplyController extends AbstractController
{
    #[Route('/', name: 'supplies_supply_index', methods: ['GET'])]
    public function index(HouseholdRepository $householdRepository, SessionInterface $session): Response
    {
        $currentHousehold = $householdRepository->find($session->get('current_household'));

        return $this->render('supplies/supply/index.html.twig', [
            'pageTitle' => t('Supplies'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'supplies_supply_datatables', methods: ['GET'])]
    public function getAsDatatables(Request $request, SupplyService $supplyService, HouseholdRepository $householdRepository, SessionInterface $session): Response
    {
        $currentHousehold = $householdRepository->find($session->get('current_household'));

        return $this->json(
            $supplyService->getSuppliesAsDatatablesArray($request, $currentHousehold)
        );
    }

    #[Route('/select2', name: 'supplies_supply_select2', methods: ['GET'])]
    public function getAsSelect2(Request $request, SupplyService $supplyService, HouseholdRepository $householdRepository, SessionInterface $session): Response
    {
        $currentHousehold = $householdRepository->find($session->get('current_household'));

        return $this->json(
            $supplyService->getSuppliesAsSelect2Array($request, $currentHousehold)
        );
    }

    #[Route('/new', name: 'supplies_supply_new', methods: ['GET', 'POST'])]
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

        $this->denyAccessUnlessGranted('createSuppliesSupply', $household);

        $supply = new Supply();
        $createSupply = new SupplyDTO();

        $form = $this->createForm(SupplyType::class, $createSupply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $supply->setName($createSupply->getName());
                $supply->setCategory($createSupply->getCategory());
                $supply->setMinimumNumber($createSupply->getMinimumNumber() > 0 ? $createSupply->getMinimumNumber() : null);
                $supply->setHousehold($household);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($supply);
                $entityManager->flush();

                $this->addFlash('success', t('Supply was added.'));

                return $this->redirectToRoute('supplies_supply_index');
            }catch (\Exception) {
                $this->addFlash('error', t('Supply could not be created.'));
            }
        }

        return $this->render('supplies/supply/form.html.twig', [
            'pageTitle' => t('Add supply'),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'supplies_supply_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Supply $supply): Response
    {
        $this->denyAccessUnlessGranted('edit', $supply);

        $editSupply = new SupplyDTO();
        $editSupply->setName($supply->getName());
        $editSupply->setCategory($supply->getCategory());
        $editSupply->setMinimumNumber($supply->getMinimumNumber());

        $form = $this->createForm(SupplyType::class, $editSupply, ['supply' => $supply]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $supply->setName($editSupply->getName());
                $supply->setCategory($editSupply->getCategory());
                $supply->setMinimumNumber($editSupply->getMinimumNumber() > 0 ? $editSupply->getMinimumNumber() : null);

                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', t('Supply was updated.'));

                return $this->redirectToRoute('supplies_supply_index');
            }catch(\Exception) {
                $this->addFlash('error', t('Supply could not be updated.'));
            }
        }

        return $this->render('supplies/supply/form.html.twig', [
            'pageTitle' => t('Edit supply'),
            'form' => $form->createView(),
            'supply' => $supply,
            'button_label' => t('Update'),
        ]);
    }

    #[Route('/{id}', name: 'supplies_supply_delete', methods: ['POST'])]
    public function delete(Request $request, Supply $supply): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_supply_' . $supply->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete' , $supply);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($supply);
                $entityManager->flush();
                $this->addFlash('success', t('Supply was deleted.'));
            }else {
                throw new \Exception('invalid CSRF token');
            }
        }catch (\Exception) {
            $this->addFlash('error', t('Supply could not be deleted.'));
        }

        return $this->redirectToRoute('supplies_supply_index');
    }
}
