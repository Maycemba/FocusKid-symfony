<?php

namespace App\Form;

use App\Entity\Emotion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class EmotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'émotion',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de l\'émotion est obligatoire']),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne doit pas dépasser {{ limit }} caractères',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres et des espaces',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Joie, Tristesse, Colère...'
                ]
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Image',
                'mapped' => false, // Important: ne pas mapper directement à l'entité
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 2 Mo',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, GIF, WEBP)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Emotion::class,
        ]);
    }
}