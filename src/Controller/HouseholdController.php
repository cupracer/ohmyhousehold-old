<?php

namespace App\Controller;

use App\Entity\DTO\UpdateHousehold;
use App\Entity\Household;
use App\Form\HouseholdFormType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[Route('/{_locale<%app.supported_locales%>}/household')]
class HouseholdController extends AbstractController
{
    #[Route('/show/{id}', name: 'omh_household_show', methods: ['GET'])]
    public function show(Household $household): Response
    {
        $this->denyAccessUnlessGranted('view', $household);

        return $this->render('household/show.html.twig', [
            'pageTitle' => t('Household'),
            'household' => $household,
        ]);
    }

    #[Route('/{id}/edit', name: 'omh_household_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Household $household): Response
    {
        $this->denyAccessUnlessGranted('edit', $household);

        $updateHousehold = new UpdateHousehold();
        $updateHousehold->setTitle($household->getTitle());

        $form = $this->createForm(HouseholdFormType::class, $updateHousehold);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $household->setTitle($updateHousehold->getTitle());

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('omh_household_show', ['id' => $household->getId()]);
        }

        return $this->render('household/details_edit.html.twig', [
            'pageTitle' => t('Edit household details'),
            'household' => $household,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/switch/{id}', name: 'omh_household_switch', methods: ['POST'])]
    public function delete(Request $request, Household $household): Response
    {
        try {
            if ($this->isCsrfTokenValid('switch_household_' . $household->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('view' , $household);
                $request->getSession()->set('current_household', $household->getId());
                $this->addFlash('success', t('Switched household.'));
            }else {
                throw new Exception('invalid CSRF token');
            }
        }catch (Exception) {
            $this->addFlash('error', t('Account holder could not be deleted.'));
        }

        return $this->redirectToRoute('app_user_settings');
    }
}
