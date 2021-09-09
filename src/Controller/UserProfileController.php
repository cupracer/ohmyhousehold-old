<?php

namespace App\Controller;

use App\Entity\DTO\UpdateUserProfile;
use App\Entity\User;
use App\Form\UserProfileFormType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use function Symfony\Component\Translation\t;

#[Route('/{_locale<%app.supported_locales%>}/user/profile')]
class UserProfileController extends AbstractController
{
    #[Route('/edit', name: 'app_user_profile_edit')]
    #[IsGranted("ROLE_USER")]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userProfile = $user->getUserProfile();

        $updateUserProfile = new UpdateUserProfile();
        $updateUserProfile->setForenames($userProfile->getForenames());
        $updateUserProfile->setSurname($userProfile->getSurname());
        $updateUserProfile->setLocale($userProfile->getLocale());

        $form = $this->createForm(UserProfileFormType::class, $updateUserProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userProfile->setForenames($updateUserProfile->getForenames());
            $userProfile->setSurname($updateUserProfile->getSurname());
            $userProfile->setLocale($updateUserProfile->getLocale());

            try {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($userProfile);
                $entityManager->flush();
                $this->addFlash("success", t(message: 'Profile has been updated.', domain: 'messages'));
            } catch (Exception) {
                $this->addFlash("error", t(message: 'Could not update profile settings.', domain: 'messages'));
            }

            return $this->redirectToRoute('app_user_settings', ['_locale' => $userProfile->getLocale()]);
        }

        return $this->render('user/settings/user_profile_edit.html.twig', [
            'userProfileForm' => $form->createView(),
            'pageTitle' => t('app.user_profile'),
        ]);
    }
}
