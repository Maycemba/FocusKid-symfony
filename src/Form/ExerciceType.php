<?php

namespace App\Form;

use App\Entity\Exercice;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ExerciceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control']
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'MÉMOIRE' => 'MÉMOIRE',
                    'ATTENTION' => 'ATTENTION',
                    'LOGIQUE' => 'LOGIQUE',
                    'CHRONO' => 'CHRONO'
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('consigne', TextareaType::class, [
                'label' => 'Consigne',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3]
            ])
            ->add('difficulte', ChoiceType::class, [
                'label' => 'Difficulté',
                'choices' => [
                    '1 - Très facile' => 1,
                    '2 - Facile' => 2,
                    '3 - Moyen' => 3,
                    '4 - Difficile' => 4,
                    '5 - Très difficile' => 5
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (secondes)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu (JSON)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5, 'id' => 'contenuJson']
            ])
            ->add('pour_tous_enfants', CheckboxType::class, [
                'label' => 'Pour tous les enfants',
                'required' => false,
                'attr' => ['class' => 'form-check-input', 'id' => 'pourTousEnfants']
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('complete', CheckboxType::class, [
                'label' => 'Complété',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('archive', CheckboxType::class, [
                'label' => 'Archivé',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            // Champ cree_par - optionnel, sera set automatiquement
            ->add('cree_par', IntegerType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'style' => 'display:none']
            ])
            // Champ date_creation - caché car auto-généré
            ->add('date_creation', null, [
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['style' => 'display:none']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Exercice::class,
        ]);
    }
}