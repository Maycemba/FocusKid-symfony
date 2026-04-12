<?php

namespace App\Form;

use App\Entity\ReponseExercice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReponseExerciceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('exercice_id')
            ->add('enfant_id')
            ->add('score')
            ->add('temps_passe')
            ->add('reponses')
            ->add('reussite')
            ->add('date_passage')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReponseExercice::class,
        ]);
    }
}
