<?php

namespace App\Form;

use App\Entity\Exercice;
use App\Entity\QuestionExercice;
use App\Entity\ReponseUserExercice;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReponseUserExerciceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reponse')
            ->add('est_correcte')
            ->add('temps_reponse')
            ->add('date_reponse')
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'id',
            ])
            ->add('exercice', EntityType::class, [
                'class' => Exercice::class,
                'choice_label' => 'id',
            ])
            ->add('questionExercice', EntityType::class, [
                'class' => QuestionExercice::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReponseUserExercice::class,
        ]);
    }
}
