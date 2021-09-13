<?php

namespace App\Form\Transaction;

use App\Entity\AccountHolder;
use App\Entity\AssetAccount;
use App\Entity\BookingCategory;
use App\Entity\DTO\AccountHolderDTO;
use App\Entity\DTO\DepositTransactionDTO;
use App\Entity\Household;
use App\Entity\HouseholdUser;
use App\Repository\Account\AssetAccountRepository;
use App\Repository\AccountHolderRepository;
use App\Repository\BookingCategoryRepository;
use App\Repository\HouseholdRepository;
use App\Repository\HouseholdUserRepository;
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
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Symfony\Component\Translation\t;

class DepositTransactionType extends AbstractType
{
    private SessionInterface $session;

    private HouseholdRepository $householdRepository;
    private HouseholdUserRepository $householdUserRepository;
    private BookingCategoryRepository $bookingCategoryRepository;
    private AssetAccountRepository $assetAccountRepository;
    private AccountHolderRepository $accountHolderRepository;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private Security $security;

    private Household $household;
    private HouseholdUser $householdUser;

    public function __construct(
        SessionInterface $session,
        HouseholdRepository $householdRepository,
        HouseholdUserRepository $householdUserRepository,
        BookingCategoryRepository $bookingCategoryRepository,
        AssetAccountRepository $assetAccountRepository,
        AccountHolderRepository $accountHolderRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Security $security
    )
    {
        $this->session = $session;
        $this->householdRepository = $householdRepository;
        $this->householdUserRepository = $householdUserRepository;
        $this->bookingCategoryRepository = $bookingCategoryRepository;
        $this->assetAccountRepository = $assetAccountRepository;
        $this->accountHolderRepository = $accountHolderRepository;

        $this->entityManager = $entityManager;
        $this->validator = $validator;
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
            ->add('bookingDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label' => t('booking_date'),
                'attr' => [
                    'class' => 'text-center',
                    'autofocus' => true,
                ],
            ])
            ->add('bookingCategory', EntityType::class, [
                'placeholder' => '',
                'class' => BookingCategory::class,
                'choices' => $this->bookingCategoryRepository->findAllGrantedByHousehold($this->household),
                'label' => t('category'),
                'attr' => [
                    'class' => 'form-control select2field',
                ],
            ])
//            ->add('source', EntityType::class, [
//                'placeholder' => '',
//                'class' => AccountHolder::class,
//                'choices' => $this->accountHolderRepository->findAllGrantedByHousehold($household),
//                'attr' => [
//                    'class' => 'form-control select2field',
//                ],
//            ])
            ->add('destination', EntityType::class, [
                'placeholder' => '',
                'class' => AssetAccount::class,
                'choices' => $this->assetAccountRepository->findAllOwnedAssetAccountsByHousehold($this->household, $this->householdUser, false, false, false, false),
                'preferred_choices' => $this->assetAccountRepository->findAllOwnedAssetAccountsByHousehold($this->household, $this->householdUser, false, true, true, true),
                'label' => t('destination'),
                'attr' => [
                    'class' => 'form-control select2field',
                ],
            ])
            ->add('amount', NumberType::class, [
                'scale' => 2,
                'label' => t('amount.financial'),
                'attr' => [
                    'class' => 'form-control text-right',
                    'placeholder' => '8,88',
                ],
            ])
            ->add('description', TextType::class, [
                'required' => false,
                'label' => t('description'),
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
                    'data-label-text' => t('visibility'),
                ]
            ])
            ->add('bookingPeriodOffset', TextType::class, [
                'attr' => [
                    'class' => 'form-control text-center',
                ],
                'label' => t('offset'),
            ])

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();

                $form->add('source', EntityType::class, [
                    'placeholder' => '',
                    'class' => AccountHolder::class,
                    'choices' => $this->accountHolderRepository->findAllGrantedByHousehold($this->household),
                    'label' => t('source'),
                    'attr' => [
                        'class' => 'form-control select2field',
                    ],
                ]);
            })

            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $depostTransactionDTO = $event->getData();
                $form = $event->getForm();

                if (!$depostTransactionDTO) {
                    return;
                }

                if($depostTransactionDTO['source'] &&
                    !is_numeric($depostTransactionDTO['source'])
                ) {
                    $accountHolderDTO = new AccountHolderDTO();
                    $accountHolderDTO->setName($depostTransactionDTO['source']);

                    $errors = $this->validator->validate($accountHolderDTO);

                    if (count($errors) > 0) {
                        foreach($errors as $error) {
                            $form->get('source')->addError(new FormError($error));
                        }
                    }else {
                        $accountHolder = new AccountHolder();
                        $accountHolder->setName($accountHolderDTO->getName());
                        $accountHolder->setHousehold($this->household);

                        $this->entityManager->persist($accountHolder);
                        $this->entityManager->flush();

                        $depostTransactionDTO['source'] = $accountHolder->getId();
                        $event->setData($depostTransactionDTO);
                    }
                }

                $form->add('source', EntityType::class, [
                    'placeholder' => '',
                    'class' => AccountHolder::class,
                    'choices' => $this->accountHolderRepository->findAllGrantedByHousehold($this->household),
                    'label' => t('source'),
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
            'data_class' => DepositTransactionDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'depositTransaction',
        ]);
    }
}
