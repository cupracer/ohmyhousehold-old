<?php

namespace App\Form;

use App\Entity\DTO\CreateApiToken;
use App\Repository\ApiTokenRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CreateApiTokenType extends AbstractType
{
    private $security;
    private ApiTokenRepository $apiTokenRepository;

    public function __construct(Security $security, ApiTokenRepository $apiTokenRepository)
    {
        $this->security = $security;
        $this->apiTokenRepository = $apiTokenRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextType::class, [
                'constraints' => [
                    new Callback([
                        $this, 'validateUniqueDescription'
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreateApiToken::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'create_api_token',
        ]);
    }

    public function validateUniqueDescription($value, ExecutionContextInterface $context)
    {
        $user = $this->security->getUser();
        $result = $this->apiTokenRepository->findBy(['user' => $user, 'description' => $value]);

        if($result) {
            $context->addViolation('This description is already used for another token.');
        }
    }
}
