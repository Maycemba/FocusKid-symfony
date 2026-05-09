<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'attr' => [
                    'placeholder' => 'Choisis ton pseudo',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Choisis un nom d\'utilisateur']),
                    new Length([
                        'min' => 3, 
                        'max' => 255,
                        'minMessage' => 'Le nom d\'utilisateur doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom d\'utilisateur ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9_]+$/',
                        'message' => 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et underscores'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Ton adresse email',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'L\'email ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ton prénom (optionnel)',
                    'class' => 'form-control'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ton nom (optionnel)',
                    'class' => 'form-control'
                ]
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ton numéro de téléphone (optionnel)',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('birthdate', DateType::class, [
                'label' => 'Date de naissance',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'JJ/MM/AAAA'
                ],
                'html5' => true,
            ])
            // ⭐ NOUVEAU : Champ de sélection du rôle
            ->add('role', ChoiceType::class, [
                'label' => 'Je suis :',
                'choices' => [
                    '👦 Un enfant' => 2,
                    '👨‍👩‍👧‍👦 Un parent ' => 0,
                    
                ],
                'expanded' => true,  // Affiche sous forme de boutons radio
                'multiple' => false,
                'required' => true,
                'attr' => [
                    'class' => 'role-choice'
                ],
                'label_attr' => [
                    'class' => 'fw-bold mb-2 d-block'
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'placeholder' => 'Ton mot de passe secret',
                        'class' => 'form-control'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => [
                        'placeholder' => 'Confirme ton mot de passe',
                        'class' => 'form-control'
                    ]
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le mot de passe est obligatoire']),
                    new Length([
                        'min' => 4,
                        'max' => 255,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le mot de passe ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J\'accepte les conditions d\'utilisation',
                'constraints' => [
                    new IsTrue(['message' => 'Tu dois accepter les conditions pour continuer'])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}