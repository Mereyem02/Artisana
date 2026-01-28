<?php

namespace App\Form;

use App\Entity\Cooperative;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CooperativeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la coopérative',
                'attr' => [
                    'placeholder' => 'Ex: Coopérative des Artisans de Fès',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nom est obligatoire'),
                    new Assert\Length(
                        min: 3,
                        max: 255,
                        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères'
                    )
                ],
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Présentez votre coopérative...',
                    'class' => 'form-control',
                    'rows' => 5
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'La description est obligatoire'),
                    new Assert\Length(min: 10, max: 2000, minMessage: 'La description doit contenir au moins 10 caractères')
                ]
            ])
            ->add('adresse', TextareaType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => 'Adresse complète',
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'L\'adresse est obligatoire')
                ]
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'placeholder' => '+212 6XX XXX XXX',
                    'class' => 'form-control'
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'Le téléphone est obligatoire'),
                    new Assert\Regex(
                        pattern: '/^(\+212|0)[\s.-]?[5-7]([\s.-]?\d){8}$/',
                        message: 'Numéro de téléphone marocain invalide (ex: +212 6 12 34 56 78 ou 0612345678)'
                    )
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'contact@cooperative.ma',
                    'class' => 'form-control'
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'L\'email est obligatoire'),
                    new Assert\Email(message: 'Email invalide')
                ]
            ])
            ->add('siteWeb', UrlType::class, [
                'label' => 'Site Web',
                'attr' => [
                    'placeholder' => 'https://www.ma-cooperative.ma',
                    'class' => 'form-control'
                ],
                'required' => false,
                'constraints' => [
                    new Assert\Url(message: 'URL invalide')
                ]
            ])
            ->add('logo', FileType::class, [
                'label' => 'Logo de la coopérative',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, WEBP)',
                    ])
                ],
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'placeholder' => 'Ex: Fès',
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('region', TextType::class, [
                'label' => 'Région',
                'attr' => [
                    'placeholder' => 'Ex: Fès-Meknès',
                    'class' => 'form-control'
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cooperative::class,
        ]);
    }
}
