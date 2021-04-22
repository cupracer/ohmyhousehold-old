<?php

namespace App\Form;

use App\Entity\DTO\UpdateUserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('forenames', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Forename(s)'
                ],
            ])
            ->add('surname', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Surname'
                ],
            ])
//            ->add('locale', LocaleType::class, [
//                'choices' => ['English' => 'en', 'Deutsch' => 'de'],
//                'choice_loader' => null,
//                'attr' => [
//                    'class' => 'form-control',
//                ],
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UpdateUserProfile::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'update_user_profile',
        ]);
    }
}
