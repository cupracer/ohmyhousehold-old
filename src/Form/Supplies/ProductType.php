<?php

namespace App\Form\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\Brand;
use App\Entity\Supplies\Measure;
use App\Entity\Supplies\Packaging;
use App\Entity\Supplies\Product;
use App\Entity\Supplies\DTO\ProductDTO;
use App\Entity\Supplies\Supply;
use App\Repository\HouseholdRepository;
use App\Repository\Supplies\BrandRepository;
use App\Repository\Supplies\MeasureRepository;
use App\Repository\Supplies\PackagingRepository;
use App\Repository\Supplies\ProductRepository;
use App\Repository\Supplies\SupplyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use function Symfony\Component\Translation\t;

class ProductType extends AbstractType
{
    private SessionInterface $session;
    private HouseholdRepository $householdRepository;
    private ProductRepository $productRepository;
    private SupplyRepository $supplyRepository;
    private BrandRepository $brandRepository;
    private MeasureRepository $measureRepository;
    private PackagingRepository $packagingRepository;

    private Household $household;

    public function __construct(
        SessionInterface $session,
        HouseholdRepository $householdRepository,
        ProductRepository $productRepository,
        SupplyRepository $supplyRepository,
        BrandRepository $brandRepository,
        MeasureRepository $measureRepository,
        PackagingRepository $packagingRepository)
    {
        $this->session = $session;
        $this->householdRepository = $householdRepository;
        $this->productRepository = $productRepository;
        $this->supplyRepository = $supplyRepository;
        $this->brandRepository = $brandRepository;
        $this->measureRepository = $measureRepository;
        $this->packagingRepository = $packagingRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->session->has('current_household')) {
            $this->household = $this->householdRepository->find($this->session->get('current_household'));
        }

        $builder
            ->add('supply', EntityType::class, [
                'placeholder' => '',
                'class' => Supply::class,
                'choices' => $this->supplyRepository->findAllGrantedByHousehold($this->household),
                'attr' => [
                    'class' => 'form-control select2field',
                ],
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'autofocus' => true,
                ],
                'required' => false,
                'constraints' => [
                    new Callback([
                        $this, 'validateUniqueName'
                    ], null, $options['product'] ),
                ],
            ])
            ->add('brand', EntityType::class, [
                'placeholder' => '',
                'class' => Brand::class,
                'choices' => $this->brandRepository->findAllGrantedByHousehold($this->household),
                'attr' => [
                    'class' => 'form-control select2field',
                ],
            ])
            ->add('ean', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('measure', EntityType::class, [
                'placeholder' => '',
                'class' => Measure::class,
                'choices' => $this->measureRepository->findAllGrantedByHousehold($this->household),
                'attr' => [
                    'class' => 'form-control select2field',
                ],
            ])
            ->add('quantity', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('organicCertification', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('packaging', EntityType::class, [
                'placeholder' => '',
                'class' => Packaging::class,
                'choices' => $this->packagingRepository->findAllGrantedByHousehold($this->household),
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
            'data_class' => ProductDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'product',
            'product'   => null,
        ]);
    }

    public function validateUniqueName($value, ExecutionContextInterface $context, $payload)
    {
        // If the form is used to update an existing item, payload is set to the original entity
        /** @var Product $product */
        $product = $payload;

        $household = null;

        if($this->session->has('current_household')) {
            $household = $this->householdRepository->find($this->session->get('current_household'));
        }

        // A household is mandatory here
        if(!$household) {
            $context->addViolation(t('Could not determine currently used household.'));
        }

        // search for existing items with the same attribute value
        $result = $this->productRepository->findBy(['name' => $value, 'household' => $household]);

        // If this form is meant to create a new item, it's sufficient to check for a non-empty result,
        // but if it's an update, we also need to check whether the original item
        // is in the result array to exclude this hit.
        if((!$product && $result) || ($product && $result && !in_array($product, $result))) {
            $context->addViolation('This name is already in use.');
        }
    }
}
