<?php
// src/Form/CommentaireType.php

namespace App\Form;

use App\Entity\Commentaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_seule', DateType::class, [
                'mapped' => false,
                'label' => '📅 Date du commentaire',
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('heure_seule', TimeType::class, [
                'mapped' => false,
                'label' => '⏰ Heure',
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('type_commentaire', ChoiceType::class, [
                'label' => '🏷️ Type',
                'required' => true,
                'choices' => [
                    'Observation' => 'Observation',
                    'Problème' => 'Problème',
                    'Suggestion' => 'Suggestion',
                    'Amélioration' => 'Amélioration',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('texte_commentaire', TextareaType::class, [
                'label' => '✏️ Votre commentaire',
                'required' => true,
                'attr' => ['rows' => 5, 'placeholder' => 'Écrivez ici...', 'class' => 'form-control'],
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
}