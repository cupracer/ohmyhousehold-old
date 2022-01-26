<?php

namespace App\Form\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\DTO\ItemEditDTO;
use App\Entity\Supplies\Product;
use App\Repository\HouseholdRepository;
use App\Repository\Supplies\ProductRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class ItemEditType extends AbstractType
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
//            //TODO: This field loads all products although this is handled by Select2.
//            // Loading all products seems to be required for validation. Is there a better way?
//            ->add('product', EntityType::class, [
//                'placeholder' => '',
//                'class' => Product::class,
//                'attr' => [
//                    'class' => 'form-control select2field',
//                    'data-json-url' => $this->router->generate('supplies_product_select2'),
//                ],
//            ])

            // The Products field is used with Select2 to load options dynamically via Ajax.
            // As Symfony would load all Products a seconds time, we generate it via EventListeners.
            // PRE_SET_DATA uses an empty array,
            // PRE_SUMIT picks the selected ID and tries to load the Product from the database.
            // Validation: If a result is returned, the object is fine, if not, the selection is wrong.

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var ItemEditDTO $data */
                $data = $event->getData();
                $form = $event->getForm();

                $productId = $data->getProduct()?->getId();

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

            ->add('bestBeforeDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'text-center',
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ItemEditDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'item_edit',
            'item'   => null,
        ]);
    }
}
