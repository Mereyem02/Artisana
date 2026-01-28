<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Artisan;
use App\Entity\Cooperative;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isAdmin = $options['is_admin'] ?? false;
        $isArtisan = $options['is_artisan'] ?? false;

        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre du produit',
                'attr' => [
                    'placeholder' => 'Ex: Tapis berbère traditionnel',
                    'class' => 'form-control'

                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le titre est obligatoire'),
                    new Assert\Length(
                        min: 3,
                        max: 255,
                        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères',
                        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères'
                    )
                ],
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Décrivez votre produit en détail...',
                    'class' => 'form-control',
                    'rows' => 5
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'La description est obligatoire'),
                    new Assert\Length(
                        min: 10,
                        max: 2000,
                        minMessage: 'La description doit contenir au moins {{ limit }} caractères',
                        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères'
                    )
                ],
                'required' => true
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (DH)',
                'attr' => [
                    'placeholder' => '0.00',
                    'class' => 'form-control',
                    'step' => '0.01'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le prix est obligatoire'),
                    new Assert\Positive(message: 'Le prix doit être positif'),
                    new Assert\Regex(
                        pattern: '/^\d+([.,]\d{1,2})?$/',
                        message: 'Le prix doit être un nombre valide (ex: 99.99)'
                    )
                ],
                'invalid_message' => 'Veuillez saisir un prix valide (ex: 99.99)',
                'required' => true


            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock disponible',
                'attr' => [
                    'placeholder' => '0',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le stock est obligatoire'),
                    new Assert\PositiveOrZero(message: 'Le stock doit être positif ou zéro'),
                    new Assert\Range(
                        min: 0,
                        max: 1000000,
                        notInRangeMessage: 'Le stock doit être compris entre {{ min }} et {{ max }}'
                    )
                ]
            ])
            ->add('dimensions', TextType::class, [
                'label' => 'Dimensions',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: 120cm x 80cm',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Les dimensions sont obligatoires'),
                    new Assert\Length(max: 255, maxMessage: 'Les dimensions ne peuvent pas dépasser {{ limit }} caractères'),
                    new Assert\Regex(
                        pattern: '/^\s*\d+(?:[\.,]\d+)?\s*(?:mm|cm|m)\s*[x×]\s*\d+(?:[\.,]\d+)?\s*(?:mm|cm|m)\s*$/i',
                        message: 'Format des dimensions invalide. Exemple attendu: 120cm x 80cm'
                    )
                ]
            ])
            ->add('materiaux', CollectionType::class, [
                'label' => 'Matériaux',
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'required' => true,
                'attr' => ['class' => 'materiaux-collection'],
                'entry_options' => [
                    'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Laine'],
                    'constraints' => [
                        new Assert\NotBlank(message: 'Chaque matériau doit être renseigné'),
                        new Assert\Length(
                            min: 2,
                            max: 100,
                            minMessage: 'Chaque matériau doit contenir au moins {{ limit }} caractères',
                            maxMessage: 'Chaque matériau ne peut pas dépasser {{ limit }} caractères'
                        )
                    ]
                ],
                'constraints' => [
                    new Assert\Count(min: 1, minMessage: 'Ajoutez au moins un matériau')
                ],
                'error_bubbling' => false
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Produit actif',
                'required' => false,
                'data' => true,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('photos', FileType::class, [
                'label' => 'Photos du produit',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new Assert\All([
                        new Assert\File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/jpg',
                                'image/webp',
                            ],
                            'mimeTypesMessage' => 'Veuillez uploader des images valides (JPG, PNG, WEBP)',
                        ])
                    ]),
                    new Assert\Count(max: 10, maxMessage: 'Vous ne pouvez pas uploader plus de {{ limit }} images')
                ],
            ])
        ;

        if ($isAdmin) {
            $builder->add('cooperative', EntityType::class, [
                'class' => Cooperative::class,
                'choice_label' => 'nom',
                'label' => 'Coopérative',
                'placeholder' => 'Sélectionnez une coopérative',
                'required' => false,
                'attr' => ['class' => 'form-select']
            ]);

            $builder->add('artisans', EntityType::class, [
                'class' => Artisan::class,
                'choice_label' => 'nom',
                'label' => 'Artisans',
                'multiple' => true,
                'required' => false,
                'attr' => ['class' => 'form-select']
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'is_admin' => false,
            'is_artisan' => false,
        ]);
    }
}
