<?php

namespace App\Controller;

use App\Entity\DTO\UpdateUserProfile;
use App\Entity\User;
use App\Form\UserProfileFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class UserProfileController extends AbstractController
{
    #[Route('/profile', name: 'user_profile')]
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

        $form = $this->createForm(UserProfileFormType::class, $updateUserProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userProfile->setForenames($updateUserProfile->getForenames());
            $userProfile->setSurname($updateUserProfile->getSurname());

            try {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($userProfile);
                $entityManager->flush();
                $this->addFlash("success", "Profile has been updated.");
            } catch (\Exception $e) {
                $this->addFlash("error", "Couldn't update profile settings.");
            }

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user_profile/index.html.twig', [
            'userProfileForm' => $form->createView(),
            'pageTitle' => 'User profile',
        ]);
    }
}
