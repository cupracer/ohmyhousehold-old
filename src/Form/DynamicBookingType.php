<?php

namespace App\Form;

use App\Entity\AccountHolder;
use App\Entity\BookingCategory;
use App\Entity\DTO\DynamicBookingDTO;
use App\Repository\HouseholdRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function Symfony\Component\Translation\t;

class DynamicBookingType extends AbstractType
{
    private SessionInterface $session;
    private HouseholdRepository $householdRepository;
    private UrlGeneratorInterface $router;

    public function __construct(
        SessionInterface $session,
        HouseholdRepository $householdRepository,
        UrlGeneratorInterface $router
    )
    {
        $this->session = $session;
        $this->householdRepository = $householdRepository;
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $household = null;

        if($this->session->has('current_household')) {
            $household = $this->householdRepository->find($this->session->get('current_household'));
        }

        $builder
            ->add('bookingDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'text-center',
                ],
            ])
            ->add('bookingCategory', EntityType::class, [
                'placeholder' => '',
                'class' => BookingCategory::class,
                'attr' => [
                    'class' => 'form-control select2field',
                    'data-json-url' => $this->router->generate('housekeepingbook_bookingcategory_select2'),
                ],
            ])
            ->add('accountHolder', EntityType::class, [
                'placeholder' => '',
                'class' => AccountHolder::class,
                'attr' => [
                    'class' => 'form-control select2field',
                    'data-json-url' => $this->router->generate('housekeepingbook_accountholder_select2'),
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
            ->add('private', CheckboxType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'data-on-text' => t('private'),
                    'data-off-text' => t('household'),
                    'data-on-color' => 'success',
                    'data-label-text' => t('Visibility'),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DynamicBookingDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'dynamicBooking',
        ]);
    }
}
