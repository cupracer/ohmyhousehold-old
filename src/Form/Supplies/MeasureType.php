<?php

namespace App\Form\Supplies;

use App\Entity\Supplies\Measure;
use App\Entity\Supplies\DTO\MeasureDTO;
use App\Repository\HouseholdRepository;
use App\Repository\Supplies\MeasureRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use function Symfony\Component\Translation\t;

class MeasureType extends AbstractType
{
    private SessionInterface $session;
    private HouseholdRepository $householdRepository;
    private MeasureRepository $measureRepository;

    public function __construct(
        SessionInterface $session,
        HouseholdRepository $householdRepository,
        MeasureRepository $measureRepository)
    {
        $this->session = $session;
        $this->householdRepository = $householdRepository;
        $this->measureRepository = $measureRepository;
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
                    ], null, $options['measure'] ),
                ],
            ])
            ->add('physicalQuantity', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                //TODO: Validate unique fields (see Measure entity)
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MeasureDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'measure',
            'measure'   => null,
        ]);
    }

    public function validateUniqueName($value, ExecutionContextInterface $context, $payload)
    {
        // If the form is used to update an existing item, payload is set to the original entity
        /** @var Measure $measure */
        $measure = $payload;

        $household = null;

        if($this->session->has('current_household')) {
            $household = $this->householdRepository->find($this->session->get('current_household'));
        }

        // A household is mandatory here
        if(!$household) {
            $context->addViolation(t('Could not determine currently used household.'));
        }

        // search for existing items with the same attribute value
        $result = $this->measureRepository->findBy(['name' => $value, 'household' => $household]);

        // If this form is meant to create a new item, it's sufficient to check for a non-empty result,
        // but if it's an update, we also need to check whether the original item
        // is in the result array to exclude this hit.
        if((!$measure && $result) || ($measure && $result && !in_array($measure, $result))) {
            $context->addViolation('This name is already in use.');
        }
    }
}
