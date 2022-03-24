<?php

namespace App\Controller\Transaction;

use App\Repository\HouseholdRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_HOUSEKEEPINGBOOK")]
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/transaction')]
class TransactionController extends AbstractController
{
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;

    public function __construct(HouseholdRepository $householdRepository, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
    }

    #[Route('/', name: 'housekeepingbook_transaction_index', methods: ['GET'])]
    public function index(): Response
    {
        $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));

        return $this->render('housekeepingbook/transaction/index.html.twig', [
            'pageTitle' => t('Transactions'),
            'household' => $currentHousehold,
        ]);
    }
}
