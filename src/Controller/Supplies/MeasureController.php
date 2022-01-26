<?php

namespace App\Controller\Supplies;

use App\Entity\Supplies\Measure;
use App\Entity\Supplies\DTO\MeasureDTO;
use App\Form\Supplies\MeasureType;
use App\Repository\HouseholdRepository;
use App\Service\Supplies\MeasureService;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_SUPPLIES")]
#[Route('/{_locale<%app.supported_locales%>}/supplies/components/measure')]
class MeasureController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/', name: 'supplies_measure_index', methods: ['GET'])]
    public function index(HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->render('supplies/measure/index.html.twig', [
            'pageTitle' => t('Measures'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/datatables', name: 'supplies_measure_datatables', methods: ['GET'])]
    public function getAsDatatables(Request $request, MeasureService $measureService, HouseholdRepository $householdRepository): Response
    {
        $currentHousehold = $householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->json(
            $measureService->getMeasuresAsDatatablesArray($request, $currentHousehold)
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

    #[Route('/new', name: 'supplies_measure_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        HouseholdRepository $householdRepository
    ): Response
    {
        $household = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $household = $householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        $this->denyAccessUnlessGranted('createSuppliesMeasure', $household);

        $measure = new Measure();
        $createMeasure = new MeasureDTO();

        $form = $this->createForm(MeasureType::class, $createMeasure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $measure->setName($createMeasure->getName());
                $measure->setPhysicalQuantity($createMeasure->getPhysicalQuantity());
                $measure->setHousehold($household);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($measure);
                $entityManager->flush();

                $this->addFlash('success', t('Measure was added.'));

                return $this->redirectToRoute('supplies_measure_new');
            }catch (Exception) {
                $this->addFlash('error', t('Measure could not be created.'));
            }
        }

        return $this->render('supplies/measure/form.html.twig', [
            'pageTitle' => t('Add measure'),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'supplies_measure_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Measure $measure): Response
    {
        $this->denyAccessUnlessGranted('edit', $measure);

        $editMeasure = new MeasureDTO();
        $editMeasure->setName($measure->getName());
        $editMeasure->setPhysicalQuantity($measure->getPhysicalQuantity());

        $form = $this->createForm(MeasureType::class, $editMeasure, ['measure' => $measure]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $measure->setName($editMeasure->getName());
                $measure->setPhysicalQuantity($editMeasure->getPhysicalQuantity());

                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', t('Measure was updated.'));

                return $this->redirectToRoute('supplies_measure_index');
            }catch(Exception) {
                $this->addFlash('error', t('Measure could not be updated.'));
            }
        }

        return $this->render('supplies/measure/form.html.twig', [
            'pageTitle' => t('Edit measure'),
            'form' => $form->createView(),
            'measure' => $measure,
            'button_label' => t('Update'),
        ]);
    }

    #[Route('/{id}', name: 'supplies_measure_delete', methods: ['POST'])]
    public function delete(Request $request, Measure $measure): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_measure_' . $measure->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete' , $measure);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($measure);
                $entityManager->flush();
                $this->addFlash('success', t('Measure was deleted.'));
            }else {
                throw new Exception('invalid CSRF token');
            }
        }catch (Exception) {
            $this->addFlash('error', t('Measure could not be deleted.'));
        }

        return $this->redirectToRoute('supplies_measure_index');
    }
}
