<?php

namespace App\Form\Supplies;

use App\Entity\Supplies\StorageLocation;
use App\Entity\Supplies\DTO\StorageLocationDTO;
use App\Repository\HouseholdRepository;
use App\Repository\Supplies\StorageLocationRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use function Symfony\Component\Translation\t;

class StorageLocationType extends AbstractType
{
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;
    private StorageLocationRepository $storageLocationRepository;

    public function __construct(
        RequestStack $requestStack,
        HouseholdRepository $householdRepository,
        StorageLocationRepository $storageLocationRepository)
    {
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
        $this->storageLocationRepository = $storageLocationRepository;
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
                    ], null, $options['storageLocation'] ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StorageLocationDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'storageLocation',
            'storageLocation'   => null,
        ]);
    }

    public function validateUniqueName($value, ExecutionContextInterface $context, $payload)
    {
        // If the form is used to update an existing item, payload is set to the original entity
        /** @var StorageLocation $storageLocation */
        $storageLocation = $payload;

        $household = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $household = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        // A household is mandatory here
        if(!$household) {
            $context->addViolation(t('Could not determine currently used household.'));
        }

        // search for existing items with the same attribute value
        $result = $this->storageLocationRepository->findBy(['name' => $value, 'household' => $household]);

        // If this form is meant to create a new item, it's sufficient to check for a non-empty result,
        // but if it's an update, we also need to check whether the original item
        // is in the result array to exclude this hit.
        if((!$storageLocation && $result) || ($storageLocation && $result && !in_array($storageLocation, $result))) {
            $context->addViolation('This name is already in use.');
        }
    }
}
