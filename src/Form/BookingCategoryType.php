<?php

namespace App\Form;

use App\Entity\BookingCategory;
use App\Entity\DTO\BookingCategoryDTO;
use App\Repository\BookingCategoryRepository;
use App\Repository\HouseholdRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use function Symfony\Component\Translation\t;

class BookingCategoryType extends AbstractType
{
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;
    private BookingCategoryRepository $bookingCategoryRepository;

    public function __construct(
        RequestStack $requestStack,
        HouseholdRepository $householdRepository,
        BookingCategoryRepository $bookingCategoryRepository)
    {
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
        $this->bookingCategoryRepository = $bookingCategoryRepository;
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
                    ], null, $options['bookingCategory'] ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BookingCategoryDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'bookingCategory',
            'bookingCategory'   => null,
        ]);
    }

    public function validateUniqueName($value, ExecutionContextInterface $context, $payload)
    {
        // If the form is used to update an existing item, payload is set to the original entity
        /** @var BookingCategory $bookingCategory */
        $bookingCategory = $payload;

        $household = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $household = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        // A household is mandatory here
        if(!$household) {
            $context->addViolation(t('Could not determine currently used household.'));
        }

        // search for existing items with the same attribute value
        $result = $this->bookingCategoryRepository->findBy(['name' => $value, 'household' => $household]);

        // If this form is meant to create a new item, it's sufficient to check for a non-empty result,
        // but if it's an update, we also need to check whether the original item
        // is in the result array to exclude this hit.
        if((!$bookingCategory && $result) || ($bookingCategory && $result && !in_array($bookingCategory, $result))) {
            $context->addViolation('This name is already in use.');
        }
    }
}
