<?php

namespace App\Controller;

use App\Entity\DTO\RegisterUser;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private string $superAdminEmail;
    private $emailVerifier;

    public function __construct(string $superAdminEmail, EmailVerifier $emailVerifier)
    {
        $this->superAdminEmail = $superAdminEmail;
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $registerUserRequest = new RegisterUser();
        $form = $this->createForm(RegistrationFormType::class, $registerUserRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();

            $user->setEmail(strtolower($registerUserRequest->getEmail()));

            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $registerUserRequest->getPlainPassword()
                )
            );

            if($user->getEmail() === strtolower($this->superAdminEmail)) {
                $user->setRoles(['ROLE_SUPER_ADMIN']);
                $user->setIsVerified(true);
            }

            $userProfile = new UserProfile();

            $userProfile->setForenames($registerUserRequest->getForenames());
            $userProfile->setSurname($registerUserRequest->getSurname());

            $user->setUserProfile($userProfile);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userProfile);
            $entityManager->persist($user);
            $entityManager->flush();

            if($user->isVerified()) {
                $this->addFlash('success', 'Successfully registered.');
            }else {
                // generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->to($user->getEmail())
                        ->subject('Please confirm your e-mail address')
                        ->textTemplate('user/registration/confirmation_email.txt.twig')
                        //->htmlTemplate('user/registration/confirmation_email.html.twig')
                );

                $this->addFlash('success',
                    'Successfully registered. An activation e-mail was sent to the provided address.');
            }

            return $this->redirectToRoute('homepage');
        }

        return $this->render('user/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->get('id'); // retrieve the user id from the url

        // Verify the user id exists and is not null
        if (null === $id) {
            return $this->redirectToRoute('homepage');
        }

        $user = $userRepository->find($id);

        // Ensure the user exists in persistence
        if (null === $user) {
            return $this->redirectToRoute('homepage');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified and your account was activated successfully.');

        return $this->redirectToRoute('app_login');
    }
}
