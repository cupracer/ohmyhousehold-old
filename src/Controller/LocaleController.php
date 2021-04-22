<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ReferrerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/locale')]
class LocaleController extends AbstractController
{
    #[Route('/{_locale<%app.supported_locales%>}/', name: 'app_user_locale')]
    public function index(string $_locale, Request $request, ReferrerService $referrerService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user) {
            $user->getUserProfile()->setLocale($request->getSession()->get('_locale'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        $referrerUrl = $referrerService->getReferrerUrl($request);

        if($referrerUrl) {
            //TODO: Do we need to use URL query parameters as well?
            return $this->redirectToRoute($referrerUrl, ['_locale' => $_locale]);
        }

        return $this->render('homepage.html.twig', [
            'controller_name' => 'HouseholdController',
        ]);
    }
}
