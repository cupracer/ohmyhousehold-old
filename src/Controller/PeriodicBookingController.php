<?php

namespace App\Controller;

use App\Entity\DTO\PeriodicBookingDTO;
use App\Entity\PeriodicBooking;
use App\Form\PeriodicBookingType;
use App\Repository\HouseholdRepository;
use App\Repository\HouseholdUserRepository;
use App\Repository\PeriodicBookingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_HOUSEKEEPINGBOOK")]
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/periodicbooking')]
class PeriodicBookingController extends AbstractController
{
    #[Route('/', name: 'housekeepingbook_periodicbooking_index', methods: ['GET'])]
    public function index(PeriodicBookingRepository $periodicBookingRepository, HouseholdRepository $householdRepository, SessionInterface $session): Response
    {
        $currentHousehold = $householdRepository->find($session->get('current_household'));

        return $this->render('housekeepingbook/periodicbooking/index.html.twig', [
            'pageTitle' => t('Periodic bookings'),
            'household' => $currentHousehold,
            'periodicBookings' => $periodicBookingRepository->findAllGrantedByHousehold($currentHousehold),
        ]);
    }

    #[Route('/new', name: 'housekeepingbook_periodicbooking_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        SessionInterface $session,
        HouseholdRepository $householdRepository,
        HouseholdUserRepository $householdUserRepository
    ): Response
    {
        $household = null;
        $householdUser = null;

        if($session->has('current_household')) {
            $household = $householdRepository->find($session->get('current_household'));
            $householdUser = $householdUserRepository->findOneByUserAndHousehold($this->getUser(), $household);
        }

        $this->denyAccessUnlessGranted('createPeriodicBooking', $household);

        $periodicBooking = new PeriodicBooking();
        $createPeriodicBooking = new PeriodicBookingDTO();

        $form = $this->createForm(PeriodicBookingType::class, $createPeriodicBooking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $periodicBooking->setStartDate($createPeriodicBooking->getStartDate());
                $periodicBooking->setEndDate($createPeriodicBooking->getEndDate());
                $periodicBooking->setBookingDayOfMonth($createPeriodicBooking->getBookingDayOfMonth());
                $periodicBooking->setInterval($createPeriodicBooking->getInterval());
                $periodicBooking->setBookingCategory($createPeriodicBooking->getBookingCategory());
                $periodicBooking->setAccountHolder($createPeriodicBooking->getAccountHolder());
                $periodicBooking->setAmount($createPeriodicBooking->getAmount());
                $periodicBooking->setDescription($createPeriodicBooking->getDescription());

                // TODO: Do we need to explicitly check that these values are set and not null?
                $periodicBooking->setHousehold($household);
                $periodicBooking->setHouseholdUser($householdUser);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($periodicBooking);
                $entityManager->flush();

                $this->addFlash('success', t('Periodic booking was added.'));

                return $this->redirectToRoute('housekeepingbook_periodicbooking_index');
            }catch(\Exception) {
                $this->addFlash('error', t('Periodic booking could not be created.'));
            }
        }

        return $this->render('housekeepingbook/periodicbooking/form.html.twig', [
            'pageTitle' => t('Add periodic booking'),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'housekeepingbook_periodicbooking_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PeriodicBooking $periodicBooking): Response
    {
        $this->denyAccessUnlessGranted('edit', $periodicBooking);

        $editPeriodicBooking = new PeriodicBookingDTO();
        $editPeriodicBooking->setStartDate($periodicBooking->getStartDate());
        $editPeriodicBooking->setEndDate($periodicBooking->getEndDate());
        $editPeriodicBooking->setBookingDayOfMonth($periodicBooking->getBookingDayOfMonth());
        $editPeriodicBooking->setInterval($periodicBooking->getInterval());
        $editPeriodicBooking->setBookingCategory($periodicBooking->getBookingCategory());
        $editPeriodicBooking->setAccountHolder($periodicBooking->getAccountHolder());
        $editPeriodicBooking->setAmount($periodicBooking->getAmount());
        $editPeriodicBooking->setDescription($periodicBooking->getDescription());

        $form = $this->createForm(PeriodicBookingType::class, $editPeriodicBooking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $periodicBooking->setStartDate($editPeriodicBooking->getStartDate());
                $periodicBooking->setEndDate($editPeriodicBooking->getEndDate());
                $periodicBooking->setBookingDayOfMonth($editPeriodicBooking->getBookingDayOfMonth());
                $periodicBooking->setInterval($editPeriodicBooking->getInterval());
                $periodicBooking->setBookingCategory($editPeriodicBooking->getBookingCategory());
                $periodicBooking->setAccountHolder($editPeriodicBooking->getAccountHolder());
                $periodicBooking->setAmount($editPeriodicBooking->getAmount());
                $periodicBooking->setDescription($editPeriodicBooking->getDescription());

                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', t('Periodic booking was updated.'));

                return $this->redirectToRoute('housekeepingbook_periodicbooking_index');
            }catch(\Exception) {
                $this->addFlash('error', t('Periodic booking could not be updated.'));
            }
        }

        return $this->render('housekeepingbook/periodicbooking/form.html.twig', [
            'pageTitle' => t('Edit periodic booking'),
            'form' => $form->createView(),
            'periodicBooking' => $periodicBooking,
            'button_label' => t('Update'),
        ]);
    }

    #[Route('/{id}', name: 'housekeepingbook_periodicbooking_delete', methods: ['POST'])]
    public function delete(Request $request, PeriodicBooking $periodicBooking): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_periodic_booking_' . $periodicBooking->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete', $periodicBooking);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($periodicBooking);
                $entityManager->flush();
                $this->addFlash('success', t('Periodic booking was deleted.'));
            }else {
                throw new \Exception('invalid CSRF token');
            }
        }catch (\Exception) {
            $this->addFlash('error', t('Periodic booking could not be deleted.'));
        }

        return $this->redirectToRoute('housekeepingbook_periodicbooking_index');
    }
}
