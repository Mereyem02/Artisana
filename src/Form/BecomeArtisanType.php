<?php

namespace App\Form;

use App\Entity\Artisan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;

class BecomeArtisanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de votre Boutique / Marque',
                'attr' => ['placeholder' => 'Ex: Artisanat du Sud'],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Le nom de votre boutique/marque est obligatoire',
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
                'label' => 'Petite description (Bio)',
                'attr' => ['rows' => 5, 'placeholder' => 'Décrivez votre savoir-faire artisanal et vos créations...'],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'La description est obligatoire',
                    ),
                    new Assert\Length(
                        min: 20,
                        max: 1000,
                        minMessage: 'La description doit contenir au moins {{ limit }} caractères',
                        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères'
                    )
                ]
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Numéro de téléphone',
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
                'label' => 'Email professionnel',
                'attr' => ['placeholder' => 'exemple@domaine.ma'],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(
                        message: "L'adresse email est obligatoire",
                    ),
                    new Assert\Email(
                        message: "Email invalide",
                    ),
                    new Assert\Regex(
                        pattern: '/\.[a-z]{2,}$/i',
                        message: "L'extension de l'email n'est pas reconnue (ex: .ma, .com, .net).",
                    )
                ]
            ])
            ->add('competences', TextType::class, [
                'label' => 'Compétences (séparées par des virgules)',
                'mapped' => false,
                'required' => true,
                'attr' => ['placeholder' => 'Ex: Poterie, Tissage, Broderie'],
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
            ->add('photo', FileType::class, [
                'label' => 'Photo de profil / Logo',
                'mapped' => false,
                'required' => true,
                'attr' => ['accept' => 'image/jpeg,image/jpg,image/png,image/webp'],
                'help' => 'Format accepté: JPEG, JPG, PNG, WEBP (max 2Mo)',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'La photo de profil/logo est obligatoire',
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artisan::class,
        ]);
    }
}
