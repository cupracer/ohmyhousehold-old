<?php

namespace App\Form;

use App\Entity\AccountHolder;
use App\Entity\BookingCategory;
use App\Entity\DTO\PeriodicBookingDTO;
use App\Repository\AccountHolderRepository;
use App\Repository\BookingCategoryRepository;
use App\Repository\HouseholdRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Symfony\Component\Translation\t;

class PeriodicBookingType extends AbstractType
{
    private SessionInterface $session;
    private BookingCategoryRepository $bookingCategoryRepository;
    private AccountHolderRepository $accountHolderRepository;
    private HouseholdRepository $householdRepository;

    public function __construct(
        SessionInterface $session,
        BookingCategoryRepository $bookingCategoryRepository,
        AccountHolderRepository $accountHolderRepository,
        HouseholdRepository $householdRepository
    )
    {
        $this->session = $session;
        $this->bookingCategoryRepository = $bookingCategoryRepository;
        $this->accountHolderRepository = $accountHolderRepository;
        $this->householdRepository = $householdRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $household = null;

        if($this->session->has('current_household')) {
            $household = $this->householdRepository->find($this->session->get('current_household'));
        }

        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'text-center',
                ],
            ])
            ->add('endDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'text-center',
                ],
            ])
            ->add('bookingDayOfMonth', NumberType::class, [
                'scale' => 0,
                'label' => t('DOM'),
                'attr' => [
                    'class' => 'form-control text-center',
                ],
            ])
            ->add('interval', NumberType::class, [
                'scale' => 0,
                'attr' => [
                    'class' => 'form-control text-center',
                    'placeholder' => '1-12',
                ],
            ])
            ->add('bookingCategory', EntityType::class, [
                'placeholder' => '',
                'class' => BookingCategory::class,
                'choices' => $this->bookingCategoryRepository->findAllGrantedByHousehold($household),
                'attr' => [
                    'class' => 'form-control select2field',
                ],
            ])
            ->add('accountHolder', EntityType::class, [
                'placeholder' => '',
                'class' => AccountHolder::class,
                'choices' => $this->accountHolderRepository->findAllGrantedByHousehold($household),
                'attr' => [
                    'class' => 'form-control select2field',
                ],
            ])
            ->add('amount', NumberType::class, [
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control text-right',
                    'placeholder' => '8,88 / -8,88',
                ],
            ])
            ->add('description', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PeriodicBookingDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'periodicbooking',
        ]);
    }
}