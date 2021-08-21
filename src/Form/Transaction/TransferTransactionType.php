<?php

namespace App\Form\Transaction;

use App\Entity\AssetAccount;
use App\Entity\BookingCategory;
use App\Entity\DTO\TransferTransactionDTO;
use App\Entity\Household;
use App\Repository\Account\AssetAccountRepository;
use App\Repository\BookingCategoryRepository;
use App\Repository\HouseholdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Symfony\Component\Translation\t;

class TransferTransactionType extends AbstractType
{
    private SessionInterface $session;

    private HouseholdRepository $householdRepository;
    private BookingCategoryRepository $bookingCategoryRepository;
    private AssetAccountRepository $assetAccountRepository;
    private Household $household;

    public function __construct(
        SessionInterface $session,
        HouseholdRepository $householdRepository,
        BookingCategoryRepository $bookingCategoryRepository,
        AssetAccountRepository $assetAccountRepository
    )
    {
        $this->session = $session;
        $this->householdRepository = $householdRepository;
        $this->bookingCategoryRepository = $bookingCategoryRepository;
        $this->assetAccountRepository = $assetAccountRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->session->has('current_household')) {
            $this->household = $this->householdRepository->find($this->session->get('current_household'));
        }

        $builder
            ->add('bookingDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'text-center',
                    'autofocus' => true,
                ],
            ])
            ->add('bookingCategory', EntityType::class, [
                'placeholder' => '',
                'class' => BookingCategory::class,
                'choices' => $this->bookingCategoryRepository->findAllGrantedByHousehold($this->household),
                'attr' => [
                    'class' => 'form-control select2field',
                ],
            ])
            ->add('source', EntityType::class, [
                'placeholder' => '',
                'class' => AssetAccount::class,
                'choices' => $this->assetAccountRepository->findAllUsableByHousehold($this->household),
                'attr' => [
                    'class' => 'form-control select2field',
                ],
            ])
            ->add('destination', EntityType::class, [
                'placeholder' => '',
                'class' => AssetAccount::class,
                'choices' => $this->assetAccountRepository->findAllUsableByHousehold($this->household),
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
            'data_class' => TransferTransactionDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'transferTransaction',
        ]);
    }
}
