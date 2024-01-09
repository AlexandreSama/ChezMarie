<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerName', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('customerFirstName', TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('customerAdress', TextType::class, [
                'label' => 'Adresse de facturation'
            ])
            ->add('customerTown', TextType::class, [
                'label' => 'Ville'
            ])
            ->add('desiredPickupDateTime', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'input' => 'datetime_immutable',
                'attr' => [
                    'min' => (new \DateTime('tomorrow'))->format('yyyy-MM-dd'),
                ],
            ])
            ->add('fullPrice', HiddenType::class)
            ->add('dateOrder', HiddenType::class)
            ->add('invoice', HiddenType::class)
            ->add('userid', HiddenType::class)
            ->add('stripeToken', HiddenType::class, [
                'mapped' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider la commande',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
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
