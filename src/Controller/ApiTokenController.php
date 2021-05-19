<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Entity\DTO\CreateApiToken;
use App\Entity\User;
use App\Form\CreateApiTokenType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/{_locale<%app.supported_locales%>}/user/apitoken')]
class ApiTokenController extends AbstractController
{
    #[IsGranted("ROLE_API")]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route('/new', name: 'app_user_apitoken_new', methods: ['GET', 'POST'])]
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

            $this->addFlash('success', 'X-AUTH-TOKEN: ' . $token);

            return $this->redirectToRoute('app_user_settings');
        }

        return $this->render('user/apitoken/new.html.twig', [
            'api_token' => $createApiToken,
            'form' => $form->createView(),
            'pageTitle' => 'Generate API token'
        ]);
    }

    #[Route('/{id}', name: 'app_user_apitoken_delete', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    #[IsGranted("ROLE_API")]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function delete(Request $request, ApiToken $apiToken): Response
    {
        if ($this->isCsrfTokenValid('delete'.$apiToken->getId(), $request->request->get('_token'))) {
            $this->denyAccessUnlessGranted('delete', $apiToken);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($apiToken);
            $entityManager->flush();
            $this->addFlash("success", "Selected API token was deleted.");
        }else {
            $this->addFlash("error", "Selected API token could not be deleted.");
        }

        return $this->redirectToRoute('app_user_settings');
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
