<?php

namespace App\Form\Transaction;

use App\Entity\AccountHolder;
use App\Entity\AssetAccount;
use App\Entity\BookingCategory;
use App\Entity\DTO\AccountHolderDTO;
use App\Entity\DTO\WithdrawalTransactionDTO;
use App\Entity\Household;
use App\Repository\Account\AssetAccountRepository;
use App\Repository\AccountHolderRepository;
use App\Repository\BookingCategoryRepository;
use App\Repository\HouseholdRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Symfony\Component\Translation\t;

class WithdrawalTransactionType extends AbstractType
{
    private SessionInterface $session;

    private HouseholdRepository $householdRepository;
    private BookingCategoryRepository $bookingCategoryRepository;
    private AssetAccountRepository $assetAccountRepository;
    private AccountHolderRepository $accountHolderRepository;
    private Household $household;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(
        SessionInterface $session,
        HouseholdRepository $householdRepository,
        BookingCategoryRepository $bookingCategoryRepository,
        AssetAccountRepository $assetAccountRepository,
        AccountHolderRepository $accountHolderRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    )
    {
        $this->session = $session;
        $this->householdRepository = $householdRepository;
        $this->bookingCategoryRepository = $bookingCategoryRepository;
        $this->assetAccountRepository = $assetAccountRepository;
        $this->accountHolderRepository = $accountHolderRepository;

        $this->entityManager = $entityManager;
        $this->validator = $validator;

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
//            ->add('destination', EntityType::class, [
//                'placeholder' => '',
//                'class' => AccountHolder::class,
//                'choices' => $this->accountHolderRepository->findAllGrantedByHousehold($this->household),
//                'attr' => [
//                    'class' => 'form-control select2field',
//                ],
//                'allow_extra_fields' => true,
//            ])
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

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();

                $form->add('destination', EntityType::class, [
                    'placeholder' => '',
                    'class' => AccountHolder::class,
                    'choices' => $this->accountHolderRepository->findAllGrantedByHousehold($this->household),
                    'attr' => [
                        'class' => 'form-control select2field',
                    ],
                ]);
            })

            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $withdrawalTransactionDTO = $event->getData();
                $form = $event->getForm();

                if (!$withdrawalTransactionDTO) {
                    return;
                }

                if($withdrawalTransactionDTO['destination'] &&
                    !is_numeric($withdrawalTransactionDTO['destination'])
                ) {
                    $accountHolderDTO = new AccountHolderDTO();
                    $accountHolderDTO->setName($withdrawalTransactionDTO['destination']);

                    $errors = $this->validator->validate($accountHolderDTO);

                    if (count($errors) > 0) {
                        foreach($errors as $error) {
                            $form->get('destination')->addError(new FormError($error));
                        }
                    }else {
                        $accountHolder = new AccountHolder();
                        $accountHolder->setName($accountHolderDTO->getName());
                        $accountHolder->setHousehold($this->household);

                        $this->entityManager->persist($accountHolder);
                        $this->entityManager->flush();

                        $withdrawalTransactionDTO['destination'] = $accountHolder->getId();
                        $event->setData($withdrawalTransactionDTO);
                    }
                }

                $form->add('destination', EntityType::class, [
                    'placeholder' => '',
                    'class' => AccountHolder::class,
                    'choices' => $this->accountHolderRepository->findAllGrantedByHousehold($this->household),
                    'attr' => [
                        'class' => 'form-control select2field',
                    ],
                ]);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WithdrawalTransactionDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'withdrawalTransaction',
        ]);
    }
}