<?php

namespace App\Controller;

use App\Entity\DTO\SwitchHousehold;
use App\Entity\DTO\UpdateHousehold;
use App\Entity\Household;
use App\Form\HouseholdFormType;
use App\Form\HouseholdType;
use App\Form\SwitchHouseholdType;
use App\Repository\HouseholdRepository;
use App\Repository\HouseholdUserRepository;
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

    #[Route('/switch', name: 'omh_household_switch', methods: ['GET', 'POST'])]
    public function switch(Request $request, HouseholdRepository $householdRepository, HouseholdUserRepository $householdUserRepository): Response
    {
        $households = $householdRepository->findByMember($this->getUser());

        if(count($households) > 1) {
            $switchHousehold = new SwitchHousehold();

            if($request->getSession()->has('current_household')) {
                $switchHousehold->setHousehold($householdRepository->find($request->getSession()->get('current_household')));
            }

            $form = $this->createForm(SwitchHouseholdType::class, $switchHousehold, [
                'action' => $this->generateUrl('omh_household_switch'),
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->denyAccessUnlessGranted('view', $switchHousehold->getHousehold());
                $request->getSession()->set('current_household', $switchHousehold->getHousehold()->getId());

                return $this->redirectToRoute('homepage');
            }

            return $this->render('household/_switch.html.twig', [
                'switchHousehold' => $switchHousehold,
                'form' => $form->createView(),
            ]);
        }else {
            return new Response(null);
        }
    }
}
