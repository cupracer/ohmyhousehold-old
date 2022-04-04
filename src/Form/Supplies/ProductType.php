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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Symfony\Component\Translation\t;

class ProductType extends AbstractType
{
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;
    private ProductRepository $productRepository;
    private SupplyRepository $supplyRepository;
    private BrandRepository $brandRepository;
    private MeasureRepository $measureRepository;
    private PackagingRepository $packagingRepository;
    private UrlGeneratorInterface $router;
    private TranslatorInterface $translator;

    private Household $household;

    public function __construct(
        RequestStack $requestStack,
        HouseholdRepository $householdRepository,
        ProductRepository $productRepository,
        SupplyRepository $supplyRepository,
        BrandRepository $brandRepository,
        MeasureRepository $measureRepository,
        PackagingRepository $packagingRepository,
        UrlGeneratorInterface $router,
        TranslatorInterface $translator)
    {
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
        $this->productRepository = $productRepository;
        $this->supplyRepository = $supplyRepository;
        $this->brandRepository = $brandRepository;
        $this->measureRepository = $measureRepository;
        $this->packagingRepository = $packagingRepository;
        $this->router = $router;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->requestStack->getSession()->has('current_household')) {
            $this->household = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        $builder
//            ->add('supply', EntityType::class, [
//                'placeholder' => '',
//                'class' => Supply::class,
//                'choices' => $this->supplyRepository->findAllGrantedByHousehold($this->household),
//                'attr' => [
//                    'class' => 'form-control select2field',
//                ],
//            ])
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
//            ->add('brand', EntityType::class, [
//                'placeholder' => '',
//                'class' => Brand::class,
//                'choices' => $this->brandRepository->findAllGrantedByHousehold($this->household),
//                'attr' => [
//                    'class' => 'form-control select2field',
//                ],
//            ])
            ->add('ean', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('measure', EntityType::class, [
                'placeholder' => '',
                'class' => Measure::class,
                'choices' => $this->measureRepository->findAll(),
                'choice_label' => function(?Measure $measure) {
                    return $measure ? $this->translator->trans($measure->getName()) : '';
                },
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

            // The Supply field is used with Select2 to load options dynamically via Ajax.
            // As Symfony would load all Supplies a second time, we generate it via EventListeners.
            // PRE_SET_DATA uses an empty array,
            // PRE_SUMIT picks the selected ID and tries to load the Supply from the database.
            // Validation: If a result is returned, the object is fine, if not, the selection is wrong.

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var ProductDTO $data */
                $data = $event->getData();
                $form = $event->getForm();

                $supplyId = $data->getSupply()?->getId();
                $brandId = $data->getBrand()?->getId();

                $form
                    ->add('supply', EntityType::class, [
                        'placeholder' => '',
                        'class' => Supply::class,
                        'choices' => $this->supplyRepository->findGrantedByHouseholdAndId($this->household, intval($supplyId)),
                        'attr' => [
                            'class' => 'form-control select2field',
                            'data-json-url' => $this->router->generate('supplies_supply_select2'),
                        ],
                    ])
                    ->add('brand', EntityType::class, [
                        'placeholder' => '',
                        'class' => Brand::class,
                        'choices' => $this->brandRepository->findGrantedByHouseholdAndId($this->household, intval($brandId)),
                        'attr' => [
                            'class' => 'form-control select2field',
                            'data-json-url' => $this->router->generate('supplies_brand_select2'),
                        ],
                    ])
                ;
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                // TODO: Is it safe enough to use intval() on the id?

                $supplyId = array_key_exists('supply', $data) ? $data['supply'] : null;
                $brandId = array_key_exists('brand', $data) ? $data['brand'] : null;

                $form
                    ->add('supply', EntityType::class, [
                        'placeholder' => '',
                        'class' => Supply::class,
                        'choices' => $this->supplyRepository->findGrantedByHouseholdAndId($this->household, intval($supplyId)),
                        'attr' => [
                            'class' => 'form-control select2field',
                            'data-json-url' => $this->router->generate('supplies_supply_select2'),
                        ],
                    ])
                    ->add('brand', EntityType::class, [
                        'placeholder' => '',
                        'class' => Brand::class,
                        'choices' => $this->brandRepository->findGrantedByHouseholdAndId($this->household, intval($brandId)),
                        'attr' => [
                            'class' => 'form-control select2field',
                            'data-json-url' => $this->router->generate('supplies_brand_select2'),
                        ],
                    ])
                ;
            })
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

        /** @var Supply $supply */
        $supply = $context->getRoot()->get('supply')->getData();

        /** @var Brand $brand */
        $brand = $context->getRoot()->get('brand')->getData();

        /** @var Measure $measure */
        $measure = $context->getRoot()->get('measure')->getData();

        /** @var string $quantity */
        $quantity = $context->getRoot()->get('quantity')->getData();

        /** @var Packaging $packaging */
        $packaging = $context->getRoot()->get('packaging')->getData();

        $household = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $household = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        // A household is mandatory
        if(!$household) {
            $context->addViolation(t('Could not determine currently used household.'));
        }

        // A supply is mandatory
        if(!$supply) {
            $context->addViolation(t('Could not determine a selected supply.'));
        }

        // A brand is mandatory
        if(!$brand) {
            $context->addViolation(t('Could not determine a selected brand.'));
        }

        // A measure is mandatory
        if(!$measure) {
            $context->addViolation(t('Could not determine a selected measure.'));
        }

        // A quantity is mandatory
        if(!$quantity) {
            $context->addViolation(t('Could not determine a selected quantity.'));
        }

        // A packaging is mandatory
        if(!$packaging) {
            $context->addViolation(t('Could not determine a selected packaging.'));
        }

        // search for existing product items with the same attribute values
        $result = $this->productRepository->findBy([
            'name' => $value,
            'supply' => $supply,
            'brand' => $brand,
            'measure' => $measure,
            'quantity' => $quantity,
            'packaging' => $packaging,
            'household' => $household,
        ]);

        // If this form is meant to create a new item, it's sufficient to check for a non-empty result,
        // but if it's an update, we also need to check whether the original item
        // is in the result array to exclude this hit.
        if((!$product && $result) || ($product && $result && !in_array($product, $result))) {
            $context->addViolation('This name is already in use for the selected attributes.');
        }
    }
}
