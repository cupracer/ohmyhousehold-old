<?php

namespace App\Form;

use App\Entity\DTO\SwitchHousehold;
use App\Entity\Household;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SwitchHouseholdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('household', EntityType::class, [
                'class' => Household::class,
                'attr' => [
                    'class' => 'custom-select',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SwitchHousehold::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'switch_household',
        ]);
    }
}
