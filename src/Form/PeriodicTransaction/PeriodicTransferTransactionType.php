<?php

namespace App\Form\PeriodicTransaction;

use App\Entity\AssetAccount;
use App\Entity\BookingCategory;
use App\Entity\DTO\PeriodicTransferTransactionDTO;
use App\Entity\Household;
use App\Entity\HouseholdUser;
use App\Repository\Account\AssetAccountRepository;
use App\Repository\BookingCategoryRepository;
use App\Repository\HouseholdRepository;
use App\Repository\HouseholdUserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use function Symfony\Component\Translation\t;

class PeriodicTransferTransactionType extends AbstractType
{
    private SessionInterface $session;

    private HouseholdRepository $householdRepository;
    private HouseholdUserRepository $householdUserRepository;
    private AssetAccountRepository $assetAccountRepository;
    private Security $security;

    private Household $household;
    private HouseholdUser $householdUser;


    public function __construct(
        SessionInterface $session,
        HouseholdRepository $householdRepository,
        HouseholdUserRepository $householdUserRepository,
        AssetAccountRepository $assetAccountRepository,
        Security $security
    )
    {
        $this->session = $session;
        $this->householdRepository = $householdRepository;
        $this->householdUserRepository = $householdUserRepository;
        $this->assetAccountRepository = $assetAccountRepository;

        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->session->has('current_household')) {
            $this->household = $this->householdRepository->find($this->session->get('current_household'));
            $this->householdUser = $this->householdUserRepository->findOneByUserAndHousehold(
                $this->security->getUser(), $this->household);
        }

        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'text-center',
                    'autofocus' => true,
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
            ->add('bookingDayOfMonth', TextType::class, [
                'label' => t('DOM'),
                'attr' => [
                    'class' => 'form-control text-center',
                ],
            ])
            ->add('source', EntityType::class, [
                'placeholder' => '',
                'class' => AssetAccount::class,
                'choices' => $this->assetAccountRepository->findAllUsableByHousehold($this->household, $this->householdUser),
                'attr' => [
                    'class' => 'form-control select2field',
                ],
            ])
            ->add('destination', EntityType::class, [
                'placeholder' => '',
                'class' => AssetAccount::class,
                'choices' => $this->assetAccountRepository->findAllViewableByHousehold($this->household),
                'attr' => [
                    'class' => 'form-control select2field',
                ],
            ])
            ->add('amount', NumberType::class, [
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control text-right',
                    'placeholder' => '8,88',
                ],
            ])
            ->add('description', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('bookingInterval', TextType::class, [
                'attr' => [
                    'class' => 'form-control text-center',
                ],
                'label' => t('Interval'),
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
            ->add('bookingPeriodOffset', TextType::class, [
                'attr' => [
                    'class' => 'form-control text-center',
                ],
                'label' => t('Offset'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PeriodicTransferTransactionDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'periodicTransferTransaction',
        ]);
    }
}
