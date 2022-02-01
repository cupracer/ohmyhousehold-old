<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ReferrerService;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultController extends AbstractController
{
    private ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/', name: 'start')]
    public function homepageNoLocale(SessionInterface $session): Response
    {
        if($session->get('_locale')) {
            return $this->redirectToRoute('homepage', ['_locale' => $session->get('_locale')]);
        }else {
            return $this->redirectToRoute('homepage', ['_locale' => 'en']);
        }
    }

    #[Route('/{_locale<%app.supported_locales%>}/', name: 'homepage')]
    public function homepage(): Response
    {
        return $this->render('homepage.html.twig');
    }

    #[Route('/user/locale/{_locale<%app.supported_locales%>}/', name: 'app_user_locale')]
    public function index(string $_locale, Request $request, ReferrerService $referrerService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user) {
            $user->getUserProfile()->setLocale($request->getSession()->get('_locale'));
            $em = $this->managerRegistry->getManager();
            $em->persist($user);
            $em->flush();
        }

        $referrerUrl = $referrerService->getReferrerUrl($request);

        if($referrerUrl) {
            //TODO: Do we need to use URL query parameters as well?
            return $this->redirectToRoute($referrerUrl, ['_locale' => $_locale]);
        }

        return $this->redirectToRoute('homepage', ['_locale' => $_locale]);
    }

    #[Route('/api/ping', name: 'api_ping')]
    public function apiPing(): Response
    {
        return new JsonResponse([
            'success' => true,
            'message' => "pong"
        ]);
    }

    #[Route('/api/authcheck', name: 'api_authcheck')]
    #[IsGranted("ROLE_API")]
    public function apiAuthCheck(TranslatorInterface $translator): Response
    {
        return new JsonResponse([
            'success' => true,
            'message' => $translator->trans('Authenticated.')
        ]);
    }

    #[Route('/{_locale<%app.supported_locales%>}/datatables/locale', name: 'app_datatables_locale')]
    public function datatablesLocale(string $_locale, Packages $packages): Response
    {
        $fileName = match ($_locale) {
            "de" => 'de_de.json',
            default => 'English.lang',
        };

        return $this->redirect($packages->getUrl('build/datatables/i18n/' . $fileName));
    }

    /**
     * @Route("/toasts", name="app_toasts", methods={"GET"})
     * @return Response
     */
    public function getToasts(): Response
    {
        return $this->render('theme/_toasts.html.twig');
    }
}
