<?php

namespace App\Controller\Supplies;

use App\Entity\Supplies\Packaging;
use App\Entity\Supplies\DTO\PackagingDTO;
use App\Form\Supplies\PackagingType;
use App\Repository\HouseholdRepository;
use App\Service\Supplies\PackagingService;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_SUPPLIES")]
#[Route('/{_locale<%app.supported_locales%>}/supplies/components/packaging')]
class PackagingController extends AbstractController
{
    #[Route('/', name: 'supplies_packaging_index', methods: ['GET'])]
    public function index(HouseholdRepository $householdRepository, SessionInterface $session): Response
    {
        $currentHousehold = $householdRepository->find($session->get('current_household'));

        return $this->render('supplies/packaging/index.html.twig', [
            'pageTitle' => t('Packagings'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'supplies_packaging_datatables', methods: ['GET'])]
    public function getAsDatatables(Request $request, PackagingService $packagingService, HouseholdRepository $householdRepository, SessionInterface $session): Response
    {
        $currentHousehold = $householdRepository->find($session->get('current_household'));

        return $this->json(
            $packagingService->getPackagingsAsDatatablesArray($request, $currentHousehold)
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

    #[Route('/new', name: 'supplies_packaging_new', methods: ['GET', 'POST'])]
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

        $this->denyAccessUnlessGranted('createSuppliesPackaging', $household);

        $packaging = new Packaging();
        $createPackaging = new PackagingDTO();

        $form = $this->createForm(PackagingType::class, $createPackaging);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $packaging->setName($createPackaging->getName());
                $packaging->setHousehold($household);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($packaging);
                $entityManager->flush();

                $this->addFlash('success', t('Packaging was added.'));

                return $this->redirectToRoute('supplies_packaging_new');
            }catch (Exception) {
                $this->addFlash('error', t('Packaging could not be created.'));
            }
        }

        return $this->render('supplies/packaging/form.html.twig', [
            'pageTitle' => t('Add packaging'),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'supplies_packaging_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Packaging $packaging): Response
    {
        $this->denyAccessUnlessGranted('edit', $packaging);

        $editPackaging = new PackagingDTO();
        $editPackaging->setName($packaging->getName());

        $form = $this->createForm(PackagingType::class, $editPackaging, ['packaging' => $packaging]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $packaging->setName($editPackaging->getName());

                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', t('Packaging was updated.'));

                return $this->redirectToRoute('supplies_packaging_index');
            }catch(Exception) {
                $this->addFlash('error', t('Packaging could not be updated.'));
            }
        }

        return $this->render('supplies/packaging/form.html.twig', [
            'pageTitle' => t('Edit packaging'),
            'form' => $form->createView(),
            'packaging' => $packaging,
            'button_label' => t('Update'),
        ]);
    }

    #[Route('/{id}', name: 'supplies_packaging_delete', methods: ['POST'])]
    public function delete(Request $request, Packaging $packaging): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_packaging_' . $packaging->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete' , $packaging);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($packaging);
                $entityManager->flush();
                $this->addFlash('success', t('Packaging was deleted.'));
            }else {
                throw new Exception('invalid CSRF token');
            }
        }catch (Exception) {
            $this->addFlash('error', t('Packaging could not be deleted.'));
        }

        return $this->redirectToRoute('supplies_packaging_index');
    }
}
