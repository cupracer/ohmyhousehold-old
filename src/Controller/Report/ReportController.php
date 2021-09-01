<?php

namespace App\Controller\Report;

use App\Service\UserSettingsService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_HOUSEKEEPINGBOOK")]
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/report')]
class ReportController extends AbstractController
{
    private UserSettingsService $userSettingsService;

    public function __construct(UserSettingsService $userSettingsService)
    {
        $this->userSettingsService = $userSettingsService;
    }


    #[Route('/', name: 'housekeepingbook_report_index', methods: ['GET'])]
    public function index(): Response
    {
        $currentHousehold = $this->userSettingsService->getCurrentHousehold($this->getUser());

        return $this->render('housekeepingbook/report/index.html.twig', [
            'pageTitle' => t('reports.noun'),
            'household' => $currentHousehold,
        ]);
    }
}
