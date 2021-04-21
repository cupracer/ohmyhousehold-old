<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Entity\DTO\CreateApiToken;
use App\Entity\User;
use App\Form\CreateApiTokenType;
use App\Repository\ApiTokenRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/apitoken')]
class ApiTokenController extends AbstractController
{
    #[Route('/', name: 'user_api_token_index', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function index(ApiTokenRepository $apiTokenRepository): Response
    {
        return $this->render('user/api_token/index.html.twig', [
            'api_tokens' => $apiTokenRepository->findAll(),
            'pageTitle' => 'API Tokens'
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function indexSnippet(ApiTokenRepository $apiTokenRepository): Response
    {
        return $this->render('user/api_token/_index.html.twig', [
            'api_tokens' => $apiTokenRepository->findBy(['user' => $this->getUser()]),
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route('/new', name: 'user_api_token_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $createApiToken = new CreateApiToken();
        $form = $this->createForm(CreateApiTokenType::class, $createApiToken);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apiToken = new ApiToken();

            /** @var User $user */
            $user = $this->getUser();

            $apiToken->setUser($user);
            $apiToken->setDescription($createApiToken->getDescription());

            $token = $this->genRandomString(64);
            $apiToken->setToken(sha1($token));
            $apiToken->setHashAlgorithm('sha1');

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($apiToken);
            $entityManager->flush();

            $this->addFlash('success', 'Your API token: ' . $token);

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/api_token/new.html.twig', [
            'api_token' => $createApiToken,
            'form' => $form->createView(),
            'pageTitle' => 'Generate API token'
        ]);
    }

    #[Route('/{id}', name: 'user_api_token_delete', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function delete(Request $request, ApiToken $apiToken): Response
    {
        if ($this->isCsrfTokenValid('delete'.$apiToken->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($apiToken);
            $entityManager->flush();
            $this->addFlash("success", "Selected API token was deleted.");
        }else {
            $this->addFlash("error", "Selected API token could not be deleted.");
        }

        return $this->redirectToRoute('user_profile');
    }

    private function genRandomString($length = 1)
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
        $extra = "-_";

        $string = "";

        for ($p = 0; $p < $length; $p++) {
            if($p == 0 || $p == $length-1) {
                $string .= $characters[mt_rand(0, strlen($characters) - 1)];
            }else {
                $string .= ($characters . $extra)[mt_rand(0, strlen($characters . $extra) - 1)];
            }
        }

        return $string;
    }
}
