<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Ingredient;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit'
            ])
            ->add('description', TextType::class, [
                'label' => 'Description du produit'
            ])
            ->add('price', IntegerType::class, [
                'label' => 'Prix du produit'
            ])
            ->add('weight', NumberType::class, [
                'label' => 'Poids du produit'
            ])
            ->add('productQuantity', IntegerType::class, [
                'label' => 'Quantité du produit'
            ])
            ->add('is_active', CheckboxType::class, [
                'label' => 'Produit actif'
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'categoryName',
                'label' => 'Catégorie'
            ])
            ->add('ingredients', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'name',
                'label' => 'Ingrédients',
                'multiple' => true,
                'expanded' => true
            ])
            ->add('pictures', FileType::class, [
                'label' => 'Photos du produit',
                'multiple' => true,

                // unmapped means that this field is not associated to any entity property
                'mapped' => true,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez envoyer une image de type JPG ou PNG',
                    ])
                ],
            ])
            ->add('basket', HiddenType::class)
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
