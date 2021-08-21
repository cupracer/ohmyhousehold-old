<?php

namespace App\Controller\Account;

use App\Service\UserSettingsService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_HOUSEKEEPINGBOOK")]
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/account')]
class AccountController extends AbstractController
{
    private UserSettingsService $userSettingsService;

    public function __construct(UserSettingsService $userSettingsService)
    {
        $this->userSettingsService = $userSettingsService;
    }


    #[Route('/', name: 'housekeepingbook_account_index', methods: ['GET'])]
    public function index(): Response
    {
        $currentHousehold = $this->userSettingsService->getCurrentHousehold($this->getUser());

        return $this->render('housekeepingbook/account/index.html.twig', [
            'pageTitle' => t('Accounts'),
            'household' => $currentHousehold,
        ]);
    }
}
