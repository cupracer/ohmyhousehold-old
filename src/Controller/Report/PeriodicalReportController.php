<?php

namespace App\Controller\Report;

use App\Entity\User;
use App\Service\Report\ReportService;
use App\Service\UserSettingsService;
use DateTime;
use IntlDateFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_HOUSEKEEPINGBOOK")]
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/report/period')]
class PeriodicalReportController extends AbstractController
{
    private UserSettingsService $userSettingsService;

    public function __construct(UserSettingsService $userSettingsService)
    {
        $this->userSettingsService = $userSettingsService;
    }


    #[Route('/current', name: 'housekeepingbook_report_periodical_current', methods: ['GET'])]
    public function current(): Response
    {
        $now = new DateTime();

        return $this->redirectToRoute('housekeepingbook_report_periodical_index',[
            'year' => $now->format('Y'),
            'month' => $now->format('m')
        ]);
    }

    #[Route('/{year}/{month}', name: 'housekeepingbook_report_periodical_index', requirements: ['year' => '\d{4}', 'month' => '\d{1,2}'], methods: ['GET'])]
    public function index(int $year, int $month, ReportService $reportService): Response
    {
        $currentHousehold = $this->userSettingsService->getCurrentHousehold($this->getUser());
        $data = $reportService->getDataAsArray($currentHousehold, $year, $month);

        /** @var User $user */
        $user = $this->getUser();

        // Formats: https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
        $dateFormatter = new IntlDateFormatter($user->getUserProfile()->getLocale(), IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE, pattern: 'LLLL YYYY');

        return $this->render('housekeepingbook/report/periodical.html.twig', [
//            'pageTitle' => t('Current Period (' . $dateFormatter->format($data['startDate']) . ' - ' . $dateFormatter->format($data['endDate']) . ')'),
            'pageTitle' => t('period'),
            'period' => $dateFormatter->format($data['startDate']),
            'currentStartDate' => (new DateTime())->modify('first day of this month')->modify('midnight'),
            'previousStartDate' => (clone $data['startDate'])->modify('- 1 month'),
            'nextStartDate' => (clone $data['startDate'])->modify('+ 1 month'),
            'household' => $currentHousehold,
            'transactions' => $data['table'],
            'deposit' => $data['deposit'],
            'upcomingDeposit' => $data['upcomingDeposit'],
            'withdrawal' => $data['withdrawal'],
            'upcomingWithdrawal' => $data['upcomingWithdrawal'],
            'balance' => $data['balance'],
            'expectedBalance' => $data['expectedBalance'],
            'savings' => $data['savings'],
            'expectedSavings' => $data['expectedSavings'],
        ]);
    }
}
