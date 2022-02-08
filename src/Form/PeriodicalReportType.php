<?php

namespace App\Form;

use App\Entity\DTO\PeriodicalReportDTO;
use App\Entity\Household;
use App\Entity\HouseholdUser;
use App\Repository\HouseholdRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Symfony\Component\Translation\t;

class PeriodicalReportType extends AbstractType
{
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;
    private Household $household;

    public function __construct(
        RequestStack $requestStack,
        HouseholdRepository $householdRepository)
    {
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->requestStack->getSession()->has('current_household')) {
            $this->household = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        $builder
            ->add('member', EntityType::class, [
                'placeholder' => '',
                'class' => HouseholdUser::class,
                'choices' => $this->household->getHouseholdUsers(),
                'label' => false,
                'row_attr' => [
                    'class' => 'float-left mr-3',
                ],
                'attr' => [
                    'class' => 'form-control select2field',
                ],
                'required' => false,
            ])
            ->add('apply', SubmitType::class, [
                'row_attr' => [
                    'class' => 'float-right',
                ],
                'label' => t('apply.button'),
                'attr' => [
                    'class' => 'form-control btn btn-default'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PeriodicalReportDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'periodicReport',
        ]);
    }
}
