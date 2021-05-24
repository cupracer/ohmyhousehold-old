<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use App\Repository\HouseholdRepository;
use App\Repository\HouseholdUserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[IsGranted("ROLE_HOUSEKEEPINGBOOK")]
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/booking')]
class BookingController extends AbstractController
{
    #[Route('/', name: 'housekeepingbook_booking_index', methods: ['GET'])]
    public function index(BookingRepository $bookingRepository, HouseholdRepository $householdRepository, SessionInterface $session): Response
    {
        $currentHousehold = $householdRepository->find($session->get('current_household'));

        return $this->render('housekeepingbook/booking/index.html.twig', [
            'pageTitle' => t('Bookings'),
            'household' => $currentHousehold,
            'bookings' => $bookingRepository->findAllGrantedByHousehold($currentHousehold),
        ]);
    }

    #[Route('/new', name: 'housekeepingbook_booking_new', methods: ['GET', 'POST'])]
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

        $this->denyAccessUnlessGranted('createBooking', $household);

        $booking = new Booking();
        $createBooking = new \App\Entity\DTO\Booking();

        $form = $this->createForm(BookingType::class, $createBooking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                // explicitly setting to "midnight" might not be necessary for a db date field
                $booking->setBookingDate($createBooking->getBookingDate()->modify('midnight'));
                $booking->setBookingCategory($createBooking->getBookingCategory());
                $booking->setAccountHolder($createBooking->getAccountHolder());
                $booking->setAmount($createBooking->getAmount());
                $booking->setDescription($createBooking->getDescription());
                $booking->setPrivate($createBooking->getPrivate());

                // TODO: Do we need to explicitly check that these values are set and not null?
                $booking->setHousehold($household);
                $booking->setHouseholdUser($householdUser);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($booking);
                $entityManager->flush();
                $this->addFlash('success', t('Booking was added.'));
                return $this->redirectToRoute('housekeepingbook_booking_new');
            } catch (\Exception) {
                $this->addFlash('error', t('Booking could not be created.'));
            }
        }

        return $this->render('housekeepingbook/booking/form.html.twig', [
            'pageTitle' => t('Add booking'),
            'booking' => $booking,
            'createBooking' => $createBooking,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'housekeepingbook_booking_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Booking $booking): Response
    {
        $this->denyAccessUnlessGranted('edit', $booking);

        $editBooking = new \App\Entity\DTO\Booking();
        $editBooking->setBookingDate($booking->getBookingDate());
        $editBooking->setBookingCategory($booking->getBookingCategory());
        $editBooking->setAccountHolder($booking->getAccountHolder());
        $editBooking->setAmount($booking->getAmount());
        $editBooking->setDescription($booking->getDescription());
        $editBooking->setPrivate($booking->getPrivate());

        $form = $this->createForm(BookingType::class, $editBooking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $booking->setBookingDate($editBooking->getBookingDate());
                $booking->setBookingCategory($editBooking->getBookingCategory());
                $booking->setAccountHolder($editBooking->getAccountHolder());
                $booking->setAmount($editBooking->getAmount());
                $booking->setDescription($editBooking->getDescription());
                $booking->setPrivate($editBooking->getPrivate());

                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', t('Booking was updated.'));

                return $this->redirectToRoute('housekeepingbook_booking_index');
            } catch (\Exception) {
                $this->addFlash('error', t('Booking could not be updated.'));
            }
        }

        return $this->render('housekeepingbook/booking/form.html.twig', [
            'pageTitle' => t('Edit booking'),
            'booking' => $booking,
            'form' => $form->createView(),
            'button_label' => t('Update'),
        ]);
    }

    #[Route('/{id}', name: 'housekeepingbook_booking_delete', methods: ['POST'])]
    public function delete(Request $request, Booking $booking): Response
    {
        if ($this->isCsrfTokenValid('delete'.$booking->getId(), $request->request->get('_token'))) {
            $this->denyAccessUnlessGranted('delete', $booking);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($booking);
            $entityManager->flush();
        }

        return $this->redirectToRoute('housekeepingbook_booking_index');
    }
}
