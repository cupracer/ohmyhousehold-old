<?php

namespace App\Controller\Supplies;

use App\Entity\Supplies\StorageLocation;
use App\Entity\Supplies\DTO\StorageLocationDTO;
use App\Form\Supplies\StorageLocationType;
use App\Repository\HouseholdRepository;
use App\Service\Supplies\StorageLocationService;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_SUPPLIES")]
#[Route('/{_locale<%app.supported_locales%>}/supplies/components/storageLocation')]
class StorageLocationController extends AbstractController
{
    private ManagerRegistry $managerRegistry;
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack, ManagerRegistry $managerRegistry)
    {
        $this->requestStack = $requestStack;
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/', name: 'supplies_storagelocation_index', methods: ['GET'])]
    public function index(HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->render('supplies/storageLocation/index.html.twig', [
            'pageTitle' => t('Storage locations'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'supplies_storagelocation_datatables', methods: ['GET'])]
    public function getAsDatatables(Request $request, StorageLocationService $storageLocationService, HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->json(
            $storageLocationService->getStorageLocationsAsDatatablesArray($request, $currentHousehold)
        );
    }

    #[Route('/select2', name: 'supplies_storagelocation_select2', methods: ['GET'])]
    public function getAsSelect2(Request $request, StorageLocationService $storageLocationService, HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->json(
            $storageLocationService->getStorageLocationsAsSelect2Array($request, $currentHousehold)
        );
    }

    #[Route('/new', name: 'supplies_storagelocation_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        HouseholdRepository $householdRepository
    ): Response
    {
        $household = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $household = $householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        $this->denyAccessUnlessGranted('createSuppliesStorageLocation', $household);

        $storageLocation = new StorageLocation();
        $createStorageLocation = new StorageLocationDTO();

        $form = $this->createForm(StorageLocationType::class, $createStorageLocation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $storageLocation->setName($createStorageLocation->getName());
                $storageLocation->setHousehold($household);

                $entityManager = $this->managerRegistry->getManager();
                $entityManager->persist($storageLocation);
                $entityManager->flush();

                $this->addFlash('success', t('StorageLocation was added.'));

                return $this->redirectToRoute('supplies_storagelocation_new');
            }catch (Exception) {
                $this->addFlash('error', t('StorageLocation could not be created.'));
            }
        }

        return $this->render('supplies/storageLocation/form.html.twig', [
            'pageTitle' => t('Add storageLocation'),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'supplies_storagelocation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StorageLocation $storageLocation): Response
    {
        $this->denyAccessUnlessGranted('edit', $storageLocation);

        $editStorageLocation = new StorageLocationDTO();
        $editStorageLocation->setName($storageLocation->getName());

        $form = $this->createForm(StorageLocationType::class, $editStorageLocation, ['storageLocation' => $storageLocation]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $storageLocation->setName($editStorageLocation->getName());

                $this->managerRegistry->getManager()->flush();

                $this->addFlash('success', t('StorageLocation was updated.'));

                return $this->redirectToRoute('supplies_storagelocation_index');
            }catch(Exception) {
                $this->addFlash('error', t('StorageLocation could not be updated.'));
            }
        }

        return $this->render('supplies/storageLocation/form.html.twig', [
            'pageTitle' => t('Edit storageLocation'),
            'form' => $form->createView(),
            'storageLocation' => $storageLocation,
            'button_label' => t('Update'),
        ]);
    }

    #[Route('/{id}', name: 'supplies_storagelocation_delete', methods: ['POST'])]
    public function delete(Request $request, StorageLocation $storageLocation): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_storagelocation_' . $storageLocation->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete' , $storageLocation);
                $entityManager = $this->managerRegistry->getManager();
                $entityManager->remove($storageLocation);
                $entityManager->flush();
                $this->addFlash('success', t('StorageLocation was deleted.'));
            }else {
                throw new Exception('invalid CSRF token');
            }
        }catch (Exception) {
            $this->addFlash('error', t('StorageLocation could not be deleted.'));
        }

        return $this->redirectToRoute('supplies_storagelocation_index');
    }
}
