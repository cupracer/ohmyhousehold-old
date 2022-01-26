<?php

namespace App\Controller\Supplies;

use App\Entity\Supplies\DTO\ItemCheckoutDTO;
use App\Entity\Supplies\DTO\ItemEditDTO;
use App\Entity\Supplies\Item;
use App\Entity\Supplies\DTO\ItemDTO;
use App\Entity\User;
use App\Form\Supplies\ItemCheckoutType;
use App\Form\Supplies\ItemEditType;
use App\Form\Supplies\ItemType;
use App\Repository\HouseholdRepository;
use App\Repository\Supplies\ItemRepository;
use App\Service\Supplies\ItemService;
use DateTime;
use Exception;
use IntlDateFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_SUPPLIES")]
#[Route('/{_locale<%app.supported_locales%>}/supplies/item')]
class ItemController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/', name: 'supplies_item_index', methods: ['GET'])]
    public function index(HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->render('supplies/item/index.html.twig', [
            'pageTitle' => t('Items'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'supplies_item_datatables', methods: ['GET'])]
    public function getAsDatatables(Request $request, ItemService $itemService, HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

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
        HouseholdRepository $householdRepository
    ): Response
    {
        $household = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $household = $householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        $this->denyAccessUnlessGranted('createSuppliesItem', $household);

        $createItem = new ItemDTO();

        if($this->requestStack->getSession()->has('supplies_item_new_purchase_date')) {
            $createItem->setPurchaseDate($this->requestStack->getSession()->get('supplies_item_new_purchase_date'));
        }else {
            $createItem->setPurchaseDate(new DateTime());
        }

        $form = $this->createForm(ItemType::class, $createItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->requestStack->getSession()->set('supplies_item_new_purchase_date', $createItem->getPurchaseDate());

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

                return $this->redirectToRoute('supplies_item_new');
            }catch (Exception) {
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
            }catch(Exception) {
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

    #[Route('/delete/{id}', name: 'supplies_item_delete', methods: ['POST'])]
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
                throw new Exception('invalid CSRF token');
            }
        }catch (Exception) {
            $this->addFlash('error', t('Item could not be deleted.'));
        }

        return $this->redirectToRoute('supplies_item_index');
    }


    #[Route('/checkout/{id}', name: 'supplies_item_checkout', methods: ['POST'])]
    public function checkout(Request $request, Item $item): Response
    {
        $this->denyAccessUnlessGranted('checkout', $item);

        try {
            $item->setWithdrawalDate(new DateTime());

            $this->getDoctrine()->getManager()->flush();

            if($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Item was checked out.',
                    'checkinUrl' => $this->generateUrl('supplies_item_checkin', ['id' => $item->getId(),]),
                ]);
            }else {
                $this->addFlash('success', t('Item was checked out.'));
                return $this->redirectToRoute('supplies_item_index');
            }
        }catch(Exception) {
            if($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Item could not be checked out.',
                ]);
            }else {
                $this->addFlash('error', t('Item could not be checked out.'));
                return $this->redirectToRoute('supplies_item_index');
            }
        }
    }

    #[Route('/checkin/{id}', name: 'supplies_item_checkin', methods: ['POST'])]
    public function checkin(Request $request, Item $item): Response
    {
        $this->denyAccessUnlessGranted('checkin', $item);

        try {
            $item->setWithdrawalDate(null);

            $this->getDoctrine()->getManager()->flush();

            if($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Checkout was cancelled.',
                ]);
            }else {
                $this->addFlash('success', t('Checkout was cancelled.'));
                return $this->redirectToRoute('supplies_item_index');
            }
        }catch(Exception) {
            if($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Checkout could not be cancelled.',
                ]);
            }else {
                $this->addFlash('error', t('Checkout could not be cancelled.'));
                return $this->redirectToRoute('supplies_item_index');
            }
        }
    }

    #[Route('/checkout-form/{item}', name: 'supplies_item_checkout_form', methods: ['GET', 'POST'])]
    public function checkoutForm(Request $request, HouseholdRepository $householdRepository, ItemRepository $itemRepository, Item $item = null): Response
    {
        $household = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $household = $householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        $this->denyAccessUnlessGranted('checkoutSuppliesItem', $household);

        //Shortcut - if $item is set, do a checkout and return:
        if($item) {
            $this->denyAccessUnlessGranted('checkout', $item);

            $item->setWithdrawalDate(new DateTime());
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', t('Item was checked out.'));
            return $this->redirectToRoute('supplies_item_checkout_form');
        }

        $checkoutItem = new ItemCheckoutDTO();

        $form = $this->createForm(ItemCheckoutType::class, $checkoutItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $items = $itemRepository->findAllGrantedByHouseholdAndProductAndInStock($household, $checkoutItem->getProduct());

            // used to check if items have different best-before-dates
            $firstDateFound = null;
            $multipleBestBeforeDates = false;

            foreach ($items as $i) {
                if ($firstDateFound === null) {
                    $firstDateFound = $i->getBestBeforeDate();
                }elseif ($firstDateFound != $i->getBestBeforeDate()) {
                    $multipleBestBeforeDates = true;
                    break;
                }
            }

            if(($form->has('smartCheckout') && $form->get('smartCheckout')->isClicked()) && (count($items) === 1 || !$multipleBestBeforeDates)) {
                // all items should be identical, so we can use the first from the list
                $item = $items[0];

                $this->denyAccessUnlessGranted('checkout', $item);

                $item->setWithdrawalDate(new DateTime());
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', t('Item was checked out.'));
                return $this->redirectToRoute('supplies_item_checkout_form');
            }else {
                $itemsArray = [];

                /** @var User $user */
                $user = $this->getUser();
                $dateFormatter = new IntlDateFormatter($user->getUserProfile()->getLocale(), IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);

                foreach ($items as $item) {
                    $itemsArray[] = [
                        'id' => $item->getId(),
                        'bestBeforeDate' => $item->getBestBeforeDate() ? $dateFormatter->format($item->getBestBeforeDate()) : null,
                    ];
                }

                $productData = [
                    'name' => $checkoutItem->getProduct()->getSupply()->getName() . ($checkoutItem->getProduct()->getName() ? ' - ' . $checkoutItem->getProduct()->getName() : ''),
                    'brand' => $checkoutItem->getProduct()->getBrand(),
                    'amount' => 1 * $checkoutItem->getProduct()->getQuantity() . $checkoutItem->getProduct()->getMeasure()->getName(),
                ];

                return $this->render('supplies/item/form_checkout_list.html.twig', [
                    'pageTitle' => t('Checkout item'),
                    'product' => $productData,
                    'items' => $itemsArray,
                ]);
            }
        }

        return $this->render('supplies/item/form_checkout.html.twig', [
            'pageTitle' => t('Checkout item'),
            'form' => $form->createView(),
        ]);
    }
}
