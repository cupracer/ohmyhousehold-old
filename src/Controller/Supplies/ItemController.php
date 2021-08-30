<?php

namespace App\Controller\Supplies;

use App\Entity\Supplies\DTO\ItemEditDTO;
use App\Entity\Supplies\Item;
use App\Entity\Supplies\DTO\ItemDTO;
use App\Form\Supplies\ItemEditType;
use App\Form\Supplies\ItemType;
use App\Repository\HouseholdRepository;
use App\Service\Supplies\ItemService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_SUPPLIES")]
#[Route('/{_locale<%app.supported_locales%>}/supplies/item')]
class ItemController extends AbstractController
{
    #[Route('/', name: 'supplies_item_index', methods: ['GET'])]
    public function index(HouseholdRepository $householdRepository, SessionInterface $session): Response
    {
        $currentHousehold = $householdRepository->find($session->get('current_household'));

        return $this->render('supplies/item/index.html.twig', [
            'pageTitle' => t('Items'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'supplies_item_datatables', methods: ['GET'])]
    public function getAsDatatables(Request $request, ItemService $itemService, HouseholdRepository $householdRepository, SessionInterface $session): Response
    {
        $currentHousehold = $householdRepository->find($session->get('current_household'));

        return $this->json(
            $itemService->getItemsAsDatatablesArray($request, $currentHousehold)
        );
    }

//    #[Route('/select2', name: 'housekeepingbook_accountholder_select2', methods: ['GET'])]
//    public function getAsSelect2(Request $request, AccountHolderService $accountHolderService, HouseholdRepository $householdRepository, SessionInterface $session): Response
//    {
//        $currentHousehold = $householdRepository->find($session->get('current_household'));
//
//        return $this->json(
//            $accountHolderService->getAccountHoldersAsSelect2Array($request, $currentHousehold)
//        );
//    }

    #[Route('/new', name: 'supplies_item_new', methods: ['GET', 'POST'])]
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

        $this->denyAccessUnlessGranted('createSuppliesItem', $household);

        $createItem = new ItemDTO();

        if($session->has('supplies_item_new_purchase_date')) {
            $createItem->setPurchaseDate($session->get('supplies_item_new_purchase_date'));
        }else {
            $createItem->setPurchaseDate(new \DateTime());
        }

        $form = $this->createForm(ItemType::class, $createItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->set('supplies_item_new_purchase_date', $createItem->getPurchaseDate());

            try {
                for($i = 1; $i <= $createItem->getQuantity(); $i++) {
                    $item = new Item();
                    $item->setPurchaseDate($createItem->getPurchaseDate());
                    $item->setProduct($createItem->getProduct());
                    $item->setBestBeforeDate($createItem->getBestBeforeDate());
                    $item->setHousehold($household);

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($item);
                    $entityManager->flush();

                    $this->addFlash('success', t('Item %itemNumber% was added.', ['%itemNumber%' => $i]));
                }

                return $this->redirectToRoute('supplies_item_index');
            }catch (\Exception) {
                $this->addFlash('error', t('Item could not be created.'));
            }
        }

        return $this->render('supplies/item/form_new.html.twig', [
            'pageTitle' => t('Add item'),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'supplies_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Item $item): Response
    {
        $this->denyAccessUnlessGranted('edit', $item);

        $editItem = new ItemEditDTO();
        $editItem->setPurchaseDate($item->getPurchaseDate());
        $editItem->setProduct($item->getProduct());
        $editItem->setBestBeforeDate($item->getBestBeforeDate());

        $form = $this->createForm(ItemEditType::class, $editItem, ['item' => $item]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $item->setPurchaseDate($editItem->getPurchaseDate());
                $item->setProduct($editItem->getProduct());
                $item->setBestBeforeDate($editItem->getBestBeforeDate());

                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', t('Item was updated.'));

                return $this->redirectToRoute('supplies_item_index');
            }catch(\Exception) {
                $this->addFlash('error', t('Item could not be updated.'));
            }
        }

        return $this->render('supplies/item/form_edit.html.twig', [
            'pageTitle' => t('Edit item'),
            'form' => $form->createView(),
            'item' => $item,
            'button_label' => t('Update'),
        ]);
    }

    #[Route('/{id}', name: 'supplies_item_delete', methods: ['POST'])]
    public function delete(Request $request, Item $item): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_item_' . $item->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete' , $item);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($item);
                $entityManager->flush();
                $this->addFlash('success', t('Item was deleted.'));
            }else {
                throw new \Exception('invalid CSRF token');
            }
        }catch (\Exception) {
            $this->addFlash('error', t('Item could not be deleted.'));
        }

        return $this->redirectToRoute('supplies_item_index');
    }
}
