<?php

namespace App\Controller;

use App\Entity\DynamicBooking;
use App\Entity\DTO\DynamicBookingDTO;
use App\Form\DynamicBookingType;
use App\Repository\DynamicBookingRepository;
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
#[Route('/{_locale<%app.supported_locales%>}/housekeepingbook/dynamicbooking')]
class DynamicBookingController extends AbstractController
{
    #[Route('/', name: 'housekeepingbook_dynamicbooking_index', methods: ['GET'])]
    public function index(DynamicBookingRepository $dynamicBookingRepository, HouseholdRepository $householdRepository, SessionInterface $session): Response
    {
        $currentHousehold = $householdRepository->find($session->get('current_household'));

        return $this->render('housekeepingbook/dynamicbooking/index.html.twig', [
            'pageTitle' => t('Dynamic bookings'),
            'household' => $currentHousehold,
            'dynamicBookings' => $dynamicBookingRepository->findAllGrantedByHousehold($currentHousehold),
        ]);
    }

    #[Route('/new', name: 'housekeepingbook_dynamicbooking_new', methods: ['GET', 'POST'])]
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

        $dynamicBooking = new DynamicBooking();
        $createDynamicBooking = new DynamicBookingDTO();

        $form = $this->createForm(DynamicBookingType::class, $createDynamicBooking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // explicitly setting to "midnight" might not be necessary for a db date field
                $dynamicBooking->setBookingDate($createDynamicBooking->getBookingDate()->modify('midnight'));
                $dynamicBooking->setBookingCategory($createDynamicBooking->getBookingCategory());
                $dynamicBooking->setAccountHolder($createDynamicBooking->getAccountHolder());
                $dynamicBooking->setAmount($createDynamicBooking->getAmount());
                $dynamicBooking->setDescription($createDynamicBooking->getDescription());
                $dynamicBooking->setPrivate($createDynamicBooking->getPrivate());

                // TODO: Do we need to explicitly check that these values are set and not null?
                $dynamicBooking->setHousehold($household);
                $dynamicBooking->setHouseholdUser($householdUser);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($dynamicBooking);
                $entityManager->flush();

                $this->addFlash('success', t('Dynamic booking was added.'));

                return $this->redirectToRoute('housekeepingbook_dynamicbooking_new');
            } catch (\Exception) {
                $this->addFlash('error', t('Dynamic booking could not be created.'));
            }
        }

        return $this->render('housekeepingbook/dynamicbooking/form.html.twig', [
            'pageTitle' => t('Add dynamic booking'),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'housekeepingbook_dynamicbooking_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DynamicBooking $dynamicBooking): Response
    {
        $this->denyAccessUnlessGranted('edit', $dynamicBooking);

        $editDynamicBooking = new DynamicBookingDTO();
        $editDynamicBooking->setBookingDate($dynamicBooking->getBookingDate());
        $editDynamicBooking->setBookingCategory($dynamicBooking->getBookingCategory());
        $editDynamicBooking->setAccountHolder($dynamicBooking->getAccountHolder());
        $editDynamicBooking->setAmount($dynamicBooking->getAmount());
        $editDynamicBooking->setDescription($dynamicBooking->getDescription());
        $editDynamicBooking->setPrivate($dynamicBooking->getPrivate());

        $form = $this->createForm(DynamicBookingType::class, $editDynamicBooking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $dynamicBooking->setBookingDate($editDynamicBooking->getBookingDate());
                $dynamicBooking->setBookingCategory($editDynamicBooking->getBookingCategory());
                $dynamicBooking->setAccountHolder($editDynamicBooking->getAccountHolder());
                $dynamicBooking->setAmount($editDynamicBooking->getAmount());
                $dynamicBooking->setDescription($editDynamicBooking->getDescription());
                $dynamicBooking->setPrivate($editDynamicBooking->getPrivate());

                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', t('Dynamic booking was updated.'));

                return $this->redirectToRoute('housekeepingbook_dynamicbooking_index');
            } catch (\Exception) {
                $this->addFlash('error', t('Dynamic booking could not be updated.'));
            }
        }

        return $this->render('housekeepingbook/dynamicbooking/form.html.twig', [
            'pageTitle' => t('Edit dynamic booking'),
            'form' => $form->createView(),
            'dynamicBooking' => $dynamicBooking,
            'button_label' => t('Update'),
        ]);
    }

    #[Route('/{id}', name: 'housekeepingbook_dynamicbooking_delete', methods: ['POST'])]
    public function delete(Request $request, DynamicBooking $dynamicBooking): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete_dynamic_booking_' . $dynamicBooking->getId(), $request->request->get('_token'))) {
                $this->denyAccessUnlessGranted('delete', $dynamicBooking);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($dynamicBooking);
                $entityManager->flush();
                $this->addFlash('success', t('Dynamic booking was deleted.'));
            }else {
                throw new \Exception('invalid CSRF token');
            }
        }catch (\Exception) {
            $this->addFlash('error', t('Dynamic booking could not be deleted.'));
        }

        return $this->redirectToRoute('housekeepingbook_dynamicbooking_index');
    }
}
