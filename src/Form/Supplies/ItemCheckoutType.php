<?php

namespace App\Form\Supplies;

use App\Entity\Supplies\DTO\ItemCheckoutDTO;
use App\Entity\Supplies\Item;
use App\Entity\Supplies\Product;
use App\Repository\HouseholdRepository;
use App\Repository\Supplies\ItemRepository;
use App\Repository\Supplies\ProductRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function Symfony\Component\Translation\t;


class ItemCheckoutType extends AbstractType
{
    private SessionInterface $session;
    private HouseholdRepository $householdRepository;
    private ProductRepository $productRepository;
    private UrlGeneratorInterface $router;

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
        $household = null;

        if($this->session->has('current_household')) {
            $household = $this->householdRepository->find($this->session->get('current_household'));
        }

        $builder
            ->add('showItems', SubmitType::class, [
                'label' => t('show_items.button'),
            ])
            ->add('smartCheckout', SubmitType::class, [
                'label' => t('smart_checkout.button'),
                'attr' => [
                    'class' => 'btn-success',
                ],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();

                $form->add('product', EntityType::class, [
                    'placeholder' => '',
                    'class' => Product::class,
                    'choices' => [],
                    'attr' => [
                        'class' => 'form-control select2field',
                        'data-json-url' => $this->router->generate('supplies_product_inuse_select2'),
                    ],
                ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($household) {
                $data = $event->getData();
                $form = $event->getForm();

                // TODO: Is it safe enough to use intval() on the product id?

                $productId = array_key_exists('product', $data) ? $data['product'] : null;

                $form->add('product', EntityType::class, [
                    'placeholder' => '',
                    'class' => Product::class,
                    'choices' => $this->productRepository->findGrantedByHouseholdAndId($household, intval($productId)),
                    'attr' => [
                        'class' => 'form-control select2field',
                        'data-json-url' => $this->router->generate('supplies_product_inuse_select2'),
                    ],
                ]);
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ItemCheckoutDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'item_checkout',
        ]);
    }
}
