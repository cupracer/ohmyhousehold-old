<?php

namespace App\Form;

use App\Entity\AccountHolder;
use App\Entity\DTO\AccountHolderDTO;
use App\Repository\AccountHolderRepository;
use App\Repository\HouseholdRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use function Symfony\Component\Translation\t;

class AccountHolderType extends AbstractType
{
    private SessionInterface $session;
    private HouseholdRepository $householdRepository;
    private AccountHolderRepository $accountHolderRepository;

    public function __construct(
        SessionInterface $session,
        HouseholdRepository $householdRepository,
        AccountHolderRepository $accountHolderRepository)
    {
        $this->session = $session;
        $this->householdRepository = $householdRepository;
        $this->accountHolderRepository = $accountHolderRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'autofocus' => true,
                ],
                'constraints' => [
                    new Callback([
                        $this, 'validateUniqueName'
                    ], null, $options['accountHolder'] ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AccountHolderDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'accountHolder',
            'accountHolder'   => null,
        ]);
    }

    public function validateUniqueName($value, ExecutionContextInterface $context, $payload)
    {
        // If the form is used to update an existing item, payload is set to the original entity
        /** @var AccountHolder $accountHolder */
        $accountHolder = $payload;

        $household = null;

        if($this->session->has('current_household')) {
            $household = $this->householdRepository->find($this->session->get('current_household'));
        }

        // A household is mandatory here
        if(!$household) {
            $context->addViolation(t('Could not determine currently used household.'));
        }

        // search for existing items with the same attribute value
        $result = $this->accountHolderRepository->findBy(['name' => $value, 'household' => $household]);

        // If this form is meant to create a new item, it's sufficient to check for a non-empty result,
        // but if it's an update, we also need to check whether the original item
        // is in the result array to exclude this hit.
        if((!$accountHolder && $result) || ($accountHolder && $result && !in_array($accountHolder, $result))) {
            $context->addViolation('This name is already in use.');
        }
    }
}
