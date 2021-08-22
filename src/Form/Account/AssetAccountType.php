<?php

namespace App\Form\Account;

use App\Entity\AssetAccount;
use App\Entity\DTO\AssetAccountDTO;
use App\Entity\HouseholdUser;
use App\Repository\HouseholdRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Symfony\Component\Translation\t;

class AssetAccountType extends AbstractType
{
    private SessionInterface $session;

    private HouseholdRepository $householdRepository;

    public function __construct(
        SessionInterface $session,
        HouseholdRepository $householdRepository
    )
    {
        $this->session = $session;
        $this->householdRepository = $householdRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $household = null;

        if($this->session->has('current_household')) {
            $household = $this->householdRepository->find($this->session->get('current_household'));
        }

        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'autofocus' => true,
                ],
            ])
            ->add('accountType', ChoiceType::class, [
                'choices' => [
                    'Current' => AssetAccount::TYPE_CURRENT,
                    'Savings' => AssetAccount::TYPE_SAVINGS,
                ],
                'attr' => [
                    'class' => 'form-control select2field',
                    'autofocus' => true,
                ],
            ])
            ->add('iban', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('initialBalance', NumberType::class, [
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '8,88',
                ],
            ])
            ->add('initialBalanceDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'text-center',
                ],
            ])
            ->add('owners', EntityType::class, [
                'class' => HouseholdUser::class,
                'choices' => $household->getHouseholdUsers(),
                'multiple' => true,
                'attr' => [
                    'class' => 'form-control select2field',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AssetAccountDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'assetAccount',
        ]);
    }
}
