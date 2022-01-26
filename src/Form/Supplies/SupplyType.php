<?php

namespace App\Form\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\Category;
use App\Entity\Supplies\Supply;
use App\Entity\Supplies\DTO\SupplyDTO;
use App\Repository\HouseholdRepository;
use App\Repository\Supplies\CategoryRepository;
use App\Repository\Supplies\SupplyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use function Symfony\Component\Translation\t;

class SupplyType extends AbstractType
{
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;
    private SupplyRepository $supplyRepository;
    private CategoryRepository $categoryRepository;

    private Household $household;

    public function __construct(
        RequestStack $requestStack,
        HouseholdRepository $householdRepository,
        SupplyRepository $supplyRepository,
        CategoryRepository $categoryRepository)
    {
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
        $this->supplyRepository = $supplyRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->requestStack->getSession()->has('current_household')) {
            $this->household = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'autofocus' => true,
                ],
                'constraints' => [
                    new Callback([
                        $this, 'validateUniqueName'
                    ], null, $options['supply'] ),
                ],
            ])
            ->add('category', EntityType::class, [
                'placeholder' => '',
                'class' => Category::class,
                'choices' => $this->categoryRepository->findAllGrantedByHousehold($this->household),
                'attr' => [
                    'class' => 'form-control select2field',
                ],
            ])
            ->add('minimumNumber', IntegerType::class, [
                'label' => t('min #'),
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplyDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'supply',
            'supply'   => null,
        ]);
    }

    public function validateUniqueName($value, ExecutionContextInterface $context, $payload)
    {
        // If the form is used to update an existing item, payload is set to the original entity
        /** @var Supply $supply */
        $supply = $payload;

        $household = null;

        if($this->session->has('current_household')) {
            $household = $this->householdRepository->find($this->session->get('current_household'));
        }

        // A household is mandatory here
        if(!$household) {
            $context->addViolation(t('Could not determine currently used household.'));
        }

        // search for existing items with the same attribute value
        $result = $this->supplyRepository->findBy(['name' => $value, 'household' => $household]);

        // If this form is meant to create a new item, it's sufficient to check for a non-empty result,
        // but if it's an update, we also need to check whether the original item
        // is in the result array to exclude this hit.
        if((!$supply && $result) || ($supply && $result && !in_array($supply, $result))) {
            $context->addViolation('This name is already in use.');
        }
    }
}
