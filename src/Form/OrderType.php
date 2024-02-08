<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $countryCodes = [
            'France (+33)' => '+33',
            'United States (+1)' => '+1',
            'United Kingdom (+44)' => '+44',
        ];

        $builder
            ->add('customerName', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('customerFirstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('countryCode', ChoiceType::class, [
                'choices' => $countryCodes,
                'label' => 'Indicatif téléphonique',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control me-2',
                    'style' => 'width: auto;'
                ],
                // autres options selon le besoin
            ])
            ->add('phone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Numéro de téléphone'
                ],
                // Notez que vous pouvez ajouter une contrainte de validation pour s'assurer
                // que l'utilisateur entre un numéro valide selon le format attendu
            ])
            ->add('desiredPickupDateTime', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'input' => 'datetime_immutable',
                'attr' => [
                    'min' => (new \DateTime('+2 days'))->format('Y-m-d\TH:i'), // Inclure l'heure dans le format
                ],
                'label' => 'Date et heure de retrait souhaitées'
            ])
            ->add('fullPrice', HiddenType::class)
            ->add('dateOrder', HiddenType::class)
            ->add('invoice', HiddenType::class)
            ->add('userid', HiddenType::class)
            ->add('stripeToken', HiddenType::class, [
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
