<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HouseholdController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('homepage.html.twig', [
            'controller_name' => 'HouseholdController',
        ]);
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
    #[IsGranted("ROLE_USER")]
    public function apiAuthCheck(): Response
    {
        return new JsonResponse([
            'success' => true
        ]);
    }
}
