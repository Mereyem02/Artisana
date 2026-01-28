<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Entrez votre prénom',
                    'maxlength' => 100
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Le prénom est obligatoire'
                    ),
                    new Assert\Length(
                        min: 2,
                        max: 100,
                        minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères',
                        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères'
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-ZÀ-ÿ\s\-\']+$/u',
                        message: 'Le prénom ne peut contenir que des lettres, espaces, tirets et apostrophes'
                    )
                ]
            ])

            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Entrez votre nom',
                    'maxlength' => 100
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Le nom est obligatoire'
                    ),
                    new Assert\Length(
                        min: 2,
                        max: 100,
                        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
                        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-ZÀ-ÿ\s\-\']+$/u',
                        message: 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes'
                    )
                ]
            ])

            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: 0612345678 ou +212612345678',
                    'maxlength' => 20
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Le numéro de téléphone est obligatoire'
                    ),
                    new Assert\Regex(
                        pattern: '/^(\+212|0)[\s.-]?[5-7]([\s.-]?\d){8}$/',
                        message: 'Format invalide. Utilisez un numéro marocain valide (Ex: 0612345678 ou +212 6 12 34 56 78)'
                    )
                ]
            ])

            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'attr' => [
                    'placeholder' => 'votre.email@exemple.com',
                    'maxlength' => 180
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'L\'email est obligatoire'
                    ),
                    new Assert\Email(
                        message: 'L\'adresse email "{{ value }}" n\'est pas valide'
                    )
                ]
            ])

            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => true,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'placeholder' => 'Minimum 8 caractères',
                        'autocomplete' => 'new-password'
                    ],
                    'help' => 'Minimum 8 caractères, avec au moins une majuscule, une minuscule et un chiffre'
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => [
                        'placeholder' => 'Ressaisir le mot de passe',
                        'autocomplete' => 'new-password'
                    ]
                ],
                'invalid_message' => 'Les deux mots de passe doivent être identiques',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Le mot de passe est obligatoire'
                    ),
                    new Assert\Length(
                        min: 8,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères'
                    ),
                    new Assert\Regex(
                        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                        message: 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre'
                    )
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
