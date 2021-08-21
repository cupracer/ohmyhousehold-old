<?php

namespace App\Controller\PeriodicTransaction;

use App\Repository\HouseholdRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_HOUSEKEEPINGBOOK")]
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/periodictransaction')]
class PeriodicTransactionController extends AbstractController
{
    private SessionInterface $session;
    private HouseholdRepository $householdRepository;

    public function __construct(HouseholdRepository $householdRepository, SessionInterface $session)
    {
        $this->session = $session;
        $this->householdRepository = $householdRepository;
    }

    #[Route('/', name: 'housekeepingbook_periodic_transaction_index', methods: ['GET'])]
    public function index(): Response
    {
        $currentHousehold = $this->householdRepository->find($this->session->get('current_household'));

        return $this->render('housekeepingbook/periodictransaction/index.html.twig', [
            'pageTitle' => t('Periodic Transactions'),
            'household' => $currentHousehold,
        ]);
    }
}
