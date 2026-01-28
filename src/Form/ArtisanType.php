<?php

namespace App\Form;

use App\Entity\Artisan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\User;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints as Assert;
class ArtisanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'Artisan / Boutique',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Artisanat du Sud'],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Le nom de l\'artisan/boutique est obligatoire',
                    ),
                    new Assert\Length(
                        min: 3,
                        max: 150,
                        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
                        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9À-ÿ\s\-\']+$/u',
                        message: 'Le nom ne peut contenir que des lettres, chiffres, espaces, tirets et apostrophes'
                    )
                ]
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Biographie / Description',
                'attr' => ['class' => 'form-control', 'rows' => 5, 'placeholder' => 'Décrivez votre savoir-faire et vos créations...'],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'La biographie est obligatoire',
                    ),
                    new Assert\Length(
                        min: 20,
                        max: 1000,
                        minMessage: 'La biographie doit contenir au moins {{ limit }} caractères',
                        maxMessage: 'La biographie ne peut pas dépasser {{ limit }} caractères'
                    )
                ]
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Le numéro de téléphone est obligatoire',
                    ),
                    new Assert\Regex(
                        pattern: '/^(\+212|0)[\s.-]?[5-7]([\s.-]?\d){8}$/',
                        message: 'Numéro de téléphone marocain invalide (ex: 0612345678 ou +212 6 12 34 56 78)',
                    )
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'exemple@domaine.ma'
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(
                        message: "L'adresse email est obligatoire",
                    ),
                    new Assert\Email(
                        message: "Le format de l'email est invalide.",
                    ),
                    new Assert\Regex(
                        pattern: '/\.[a-z]{2,}$/i',
                        message: "L'extension de l'email n'est pas reconnue (ex: .ma, .com, .net).",
                    )
                ]
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo',
                'mapped' => false,
                'required' => $options['is_edit'] ? false : true,
                'attr' => ['class' => 'form-control', 'accept' => 'image/jpeg,image/jpg,image/png,image/webp'],
                'help' => 'Format accepté: JPEG, JPG, PNG, WEBP (max 2Mo)',
                'constraints' => $options['is_edit'] ? [] : [
                    new Assert\NotBlank(
                        message: 'La photo est obligatoire',
                    ),
                    new File(
                        maxSize: '2M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/webp',
                        ],
                        mimeTypesMessage: 'Veuillez uploader une image valide (JPEG, JPG, PNG, WEBP)',
                        maxSizeMessage: 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). La taille maximale est de {{ limit }} {{ suffix }}'
                    )
                ],
            ])
            ->add('competences', TextType::class, [
                'label' => 'Compétences (séparées par des virgules)',
                'mapped' => false,
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Poterie, Tissage, Broderie'],
                'help' => 'Listez vos compétences artisanales séparées par des virgules',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Au moins une compétence est obligatoire',
                    ),
                    new Assert\Length(
                        min: 3,
                        max: 255,
                        minMessage: 'Les compétences doivent contenir au moins {{ limit }} caractères',
                        maxMessage: 'Les compétences ne peuvent pas dépasser {{ limit }} caractères'
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-ZÀ-ÿ\s,\-]+$/u',
                        message: 'Les compétences ne peuvent contenir que des lettres, virgules, espaces et tirets'
                    )
                ]
            ])
            ->add('approvalStatus', ChoiceType::class, [
                'label' => 'Statut d\'approbation',
                'choices' => [
                    'En attente' => 'PENDING',
                    'Approuvé' => 'APPROVED',
                    'Rejeté' => 'REJECTED',
                ],
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Le statut d\'approbation est obligatoire',
                    ),
                    new Assert\Choice(
                        choices: ['PENDING', 'APPROVED', 'REJECTED'],
                        message: 'Le statut sélectionné n\'est pas valide'
                    )
                ]
            ]);

        if ($options['show_user_selection']) {
            $builder->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getEmail() . ' (' . $user->getFirstName() . ' ' . $user->getLastName() . ')';
                },
                'label' => 'Utilisateur rattaché',
                'placeholder' => 'Rechercher un utilisateur...',
                'required' => false,
                'attr' => ['class' => 'form-control select2'],
                'help' => 'Liez cet artisan à un compte utilisateur existant.'
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artisan::class,
            'is_edit' => false,
            'show_user_selection' => false,
        ]);
    }
}
