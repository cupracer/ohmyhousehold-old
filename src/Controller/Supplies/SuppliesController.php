<?php

namespace App\Controller\Supplies;

use App\Repository\HouseholdRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_SUPPLIES")]
#[Route('/{_locale<%app.supported_locales%>}/supplies')]
class SuppliesController extends AbstractController
{
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;

    public function __construct(HouseholdRepository $householdRepository, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
    }

    #[Route('/', name: 'supplies_index', methods: ['GET'])]
    public function index(): Response
    {
        $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->render('supplies/index.html.twig', [
            'pageTitle' => t('Supplies'),
            'household' => $currentHousehold,
        ]);
    }

    #[Route('/components', name: 'supplies_components_index', methods: ['GET'])]
    public function components(): Response
    {
        $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->render('supplies/components.html.twig', [
            'pageTitle' => t('Components'),
            'household' => $currentHousehold,
        ]);
    }
}
