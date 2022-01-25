<?php

namespace App\Controller\Report;

use App\Entity\DTO\PeriodicalReportDTO;
use App\Entity\User;
use App\Form\PeriodicalReportType;
use App\Repository\HouseholdUserRepository;
use App\Service\Report\ReportService;
use App\Service\UserSettingsService;
use DateTime;
use IntlDateFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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

    #[Route('/{year}/{month}', name: 'housekeepingbook_report_periodical_index', requirements: ['year' => '\d{4}', 'month' => '\d{1,2}'], methods: ['GET', 'POST'])]
    public function index(int $year, int $month, Request $request, SessionInterface $session, ReportService $reportService, HouseholdUserRepository $householdUserRepository): Response
    {
        $currentHousehold = $this->userSettingsService->getCurrentHousehold($this->getUser());

        /** @var User $user */
        $user = $this->getUser();

        $periodicalReport = new PeriodicalReportDTO();

        if($session->has('housekeepingbook_periodical_report_member_id')) {
            $id = $householdUserRepository->find($session->get('housekeepingbook_periodical_report_member_id'));
            if($id) {
                $periodicalReport->setMember($householdUserRepository->find($id));
            }
        }

        $periodicalReportForm = $this->createForm(PeriodicalReportType::class, $periodicalReport);
        $periodicalReportForm->handleRequest($request);

        if ($periodicalReportForm->isSubmitted() && $periodicalReportForm->isValid()) {
            if($periodicalReport->getMember()) {
                $session->set('housekeepingbook_periodical_report_member_id', $periodicalReport->getMember()->getId());
            }else {
                $session->remove('housekeepingbook_periodical_report_member_id');
            }
        }

        $data = $reportService->getDataAsArray($currentHousehold, $year, $month, $periodicalReport->getMember());

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
            'periodicalReportForm' => $periodicalReportForm->createView(),
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
