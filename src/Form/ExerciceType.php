<?php

namespace App\Form;

use App\Entity\Exercice;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

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
                    'MÉMOIRE' => 'MEMOIRE',
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
            // 🔥 SUPPRIMER le champ contenu d'ici - on va le gérer manuellement
            ->add('pour_tous_enfants', CheckboxType::class, [
                'label' => 'Pour tous les enfants',
                'required' => false,
                'attr' => ['class' => 'form-check-input', 'id' => 'tousEnfantsRadio']
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('archive', CheckboxType::class, [
                'label' => 'Archivé',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('enfantsSelectionnes', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'username',
                'multiple' => true,
                'required' => false,
                'mapped' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.role = :role')
                        ->setParameter('role', 'enfant')
                        ->orderBy('u.username', 'ASC');
                },
                'attr' => ['class' => 'form-select', 'size' => 5],
                'label' => 'Enfants sélectionnés'
            ])
            ->add('cree_par', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Exercice::class,
            'allow_extra_fields' => true,  // 🔥 AJOUTE CETTE LIGNE

        ]);
    }
    
}