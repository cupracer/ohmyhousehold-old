<?php

namespace App\Form\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\DTO\ItemDTO;
use App\Entity\Supplies\Product;
use App\Repository\HouseholdRepository;
use App\Repository\Supplies\ProductRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class ItemType extends AbstractType
{
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;
    private ProductRepository $productRepository;
    private UrlGeneratorInterface $router;

    private Household $household;

    public function __construct(
        RequestStack $requestStack,
        HouseholdRepository $householdRepository,
        ProductRepository $productRepository,
        UrlGeneratorInterface $router)
    {
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
        $this->productRepository = $productRepository;
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->requestStack->getSession()->has('current_household')) {
            $this->household = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        $builder
            ->add('purchaseDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'text-center',
                ],
            ])
//            ->add('product', EntityType::class, [
//                'placeholder' => '',
//                'class' => Product::class,
//                'choices' => $this->productRepository->findAllGrantedByHousehold($this->household),
//                'attr' => [
//                    'class' => 'form-control select2field',
//                ],
//            ])
            ->add('bestBeforeDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'text-center',
                ],
                'required' => false,
            ])
            ->add('quantity', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                ],
            ])

            // The Products field is used with Select2 to load options dynamically via Ajax.
            // As Symfony would load all Products a seconds time, we generate it via EventListeners.
            // PRE_SET_DATA uses an empty array,
            // PRE_SUMIT picks the selected ID and tries to load the Product from the database.
            // Validation: If a result is returned, the object is fine, if not, the selection is wrong.

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();

                $form->add('product', EntityType::class, [
                    'placeholder' => '',
                    'class' => Product::class,
                    'choices' => [],
                    'attr' => [
                        'class' => 'form-control select2field',
                        'data-json-url' => $this->router->generate('supplies_product_select2'),
                    ],
                ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                // TODO: Is it safe enough to use intval() on the product id?

                $productId = array_key_exists('product', $data) ? $data['product'] : null;

                $form->add('product', EntityType::class, [
                    'placeholder' => '',
                    'class' => Product::class,
                    'choices' => $this->productRepository->findGrantedByHouseholdAndId($this->household, intval($productId)),
                    'attr' => [
                        'class' => 'form-control select2field',
                        'data-json-url' => $this->router->generate('supplies_product_select2'),
                    ],
                ]);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ItemDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'item',
            'item'   => null,
        ]);
    }
}
