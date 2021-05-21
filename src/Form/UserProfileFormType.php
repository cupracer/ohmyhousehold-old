<?php

namespace App\Form;

use App\Entity\DTO\UpdateUserProfile;
use App\Service\LocaleService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileFormType extends AbstractType
{
    private LocaleService $localeService;

    public function __construct(LocaleService $localeService)
    {
        $this->localeService = $localeService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('forenames', TextType::class, [
                'label' => 'Forename(s)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Forename(s)',
                    'autofocus' => true,
                ],
            ])
            ->add('surname', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Surname'
                ],
            ])
            ->add('locale', LocaleType::class, [
                'choices' => $this->localeService->getSupportedLocales(true),
                'choice_loader' => null
            ])
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
