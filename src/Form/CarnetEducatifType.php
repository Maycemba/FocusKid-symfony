<?php
// src/Form/CarnetEducatifType.php

namespace App\Form;

use App\Entity\CarnetEducatif;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CarnetEducatifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_etude', DateType::class, [
                'label' => '📅 Date d\'étude',
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['class' => 'form-control form-control-lg rounded-3'],
            ])
            ->add('heure_debut', TimeType::class, [
                'label' => '⏰ Heure de début',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'class' => 'form-control form-control-lg rounded-3',
                    'id' => 'heure_debut'   // ID fixe
                ],
            ])
            ->add('heure_fin', TimeType::class, [
                'label' => '⏰ Heure de fin',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'class' => 'form-control form-control-lg rounded-3',
                    'id' => 'heure_fin'     // ID fixe
                ],
            ])
            ->add('duree_totale', IntegerType::class, [
                'label' => '⏱️ Durée totale (minutes)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control form-control-lg rounded-3',
                    'readonly' => true,
                    'id' => 'duree_totale'  // ID fixe
                ],
            ])
            ->add('lieu', TextType::class, [
                'label' => '📍 Lieu',
                'required' => true,
                'attr' => ['class' => 'form-control form-control-lg rounded-3'],
            ])
            ->add('matiere', TextType::class, [
                'label' => '📚 Matière',
                'required' => true,
                'attr' => ['class' => 'form-control form-control-lg rounded-3'],
            ])
            ->add('type_activite', TextareaType::class, [
                'label' => '🎯 Type d\'activité',
                'required' => true,
                'attr' => ['class' => 'form-control rounded-3', 'rows' => 3],
            ])
            ->add('niveau_difficulte', ChoiceType::class, [
                'label' => '⚡ Niveau de difficulté',
                'required' => true,
                'choices' => ['Facile' => 'Facile', 'Moyen' => 'Moyen', 'Difficile' => 'Difficile'],
                'attr' => ['class' => 'form-select form-select-lg rounded-3'],
            ])
            ->add('niveau_concentration', IntegerType::class, [
                'label' => '🧠 Niveau de concentration (1-5)',
                'required' => true,
                'attr' => ['class' => 'form-control form-control-lg rounded-3', 'min' => 1, 'max' => 5],
            ])
            ->add('niveau_agitation', IntegerType::class, [
                'label' => '🌀 Niveau d\'agitation (1-5)',
                'required' => true,
                'attr' => ['class' => 'form-control form-control-lg rounded-3', 'min' => 1, 'max' => 5],
            ])
            ->add('nombre_interruptions', IntegerType::class, [
                'label' => '🔔 Nombre d\'interruptions',
                'required' => true,
                'attr' => ['class' => 'form-control form-control-lg rounded-3'],
            ])
            ->add('temps_avant_perte_concentration', IntegerType::class, [
                'label' => '⏳ Temps avant perte de concentration (secondes)',
                'required' => false,
                'attr' => ['class' => 'form-control form-control-lg rounded-3'],
            ])
            ->add('travaille_seul', CheckboxType::class, [
                'label' => '🧑‍🎓 Travaille seul',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('demande_aide', CheckboxType::class, [
                'label' => '🙋 Demande de l\'aide',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('niveau_autonomie', IntegerType::class, [
                'label' => '🌟 Niveau d\'autonomie (1-5)',
                'required' => true,
                'attr' => ['class' => 'form-control form-control-lg rounded-3', 'min' => 1, 'max' => 5],
            ])
            ->add('travail_termine', CheckboxType::class, [
                'label' => '✅ Travail terminé',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('difficultes', TextareaType::class, [
                'label' => '⚠️ Difficultés rencontrées',
                'required' => false,
                'attr' => ['class' => 'form-control rounded-3', 'rows' => 4],
            ])
            ->add('points_positifs', TextareaType::class, [
                'label' => '⭐ Points positifs',
                'required' => false,
                'attr' => ['class' => 'form-control rounded-3', 'rows' => 4],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CarnetEducatif::class,
        ]);
    }
}