<?php

namespace App\Controller;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/locale')]
class LocaleController extends AbstractController
{
    #[Route('/{_locale<%app.supported_locales%>}/', name: 'app_user_locale')]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user) {
            $user->getUserProfile()->setLocale($request->getSession()->get('_locale'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        return $this->render('homepage.html.twig', [
            'controller_name' => 'HouseholdController',
        ]);
    }
}
