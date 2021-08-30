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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function Symfony\Component\Translation\t;


class ItemType extends AbstractType
{
    private SessionInterface $session;
    private HouseholdRepository $householdRepository;
    private ProductRepository $productRepository;
    private UrlGeneratorInterface $router;

    private Household $household;

    public function __construct(
        SessionInterface $session,
        HouseholdRepository $householdRepository,
        ProductRepository $productRepository,
        UrlGeneratorInterface $router)
    {
        $this->session = $session;
        $this->householdRepository = $householdRepository;
        $this->productRepository = $productRepository;
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->session->has('current_household')) {
            $this->household = $this->householdRepository->find($this->session->get('current_household'));
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

            //TODO: This field loads all products although this is handled by Select2.
            // Loading all products seems to be required for validation. Is there a better way?
            ->add('product', EntityType::class, [
                'placeholder' => '',
                'class' => Product::class,
                'attr' => [
                    'class' => 'form-control select2field',
                    'data-json-url' => $this->router->generate('supplies_product_select2'),
                ],
            ])
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
