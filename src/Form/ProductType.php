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

                'mapped' => true,

                'required' => false,

                'constraints' => [
                    new File([
                        //Taille de fichier maximum
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            //Type d'extension valide de fichier (ici webp)
                            'image/webp',
                        ],
                        //Message pour bien expliqué qu'il faut un fichier webp
                        'mimeTypesMessage' => 'Veuillez envoyer une image de type WebP',
                    ])
                ],
            ])
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
