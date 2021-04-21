<?php

namespace App\Form;

use App\Entity\DTO\RegisterUser;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class RegistrationFormType extends AbstractType
{
    private UserRepository $userRepository;


    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('forenames', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Forename(s)',
                    'autofocus' => true
                ]
            ])
            ->add('surname', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Surname'
                ]
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Callback([
                        $this, 'validateUniqueEmail'
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'E-Mail'
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'first_options'  => [
                    'label' => 'Password',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Password'
                    ]
                ],
                'second_options' => [
                    'label' => 'Repeat Password',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Retype password'
                    ],
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'I agree to the <a href="#">terms</a>',
                'label_html' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RegisterUser::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'register',
        ]);
    }

    public function validateUniqueEmail($value, ExecutionContextInterface $context)
    {
        $result = $this->userRepository->findBy(['email' => strtolower($value)]);

        if($result) {
            $context->addViolation('This e-mail address cannot be used.');
        }
    }
}
