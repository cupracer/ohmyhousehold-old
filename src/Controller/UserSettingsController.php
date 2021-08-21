<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ApiTokenRepository;
use App\Repository\HouseholdUserRepository;
use App\Service\UserSettingsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/{_locale<%app.supported_locales%>}/user/settings')]
class UserSettingsController extends AbstractController
{
    #[Route('/', name: 'app_user_settings')]
    #[IsGranted("ROLE_USER")]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function index(ApiTokenRepository $apiTokenRepository, HouseholdUserRepository $householdUserRepository, UserSettingsService $userSettingsService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $userProfile = $user->getUserProfile();
        $apiTokens = $apiTokenRepository->findBy(['user' => $this->getUser()]);
        $householdUsers = $householdUserRepository->findBy(['user' => $this->getUser()]);
        $currentHousehold = $userSettingsService->getCurrentHousehold($this->getUser());

        return $this->render('user/settings/index.html.twig', [
            'pageTitle' => t('app.user_settings'),
            'userProfile' => $userProfile,
            'householdUsers' => $householdUsers,
            'currentHousehold' => $currentHousehold,
            'apiTokens' => $apiTokens,
        ]);
    }
}
