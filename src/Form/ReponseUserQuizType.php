<?php

namespace App\Form;

use App\Entity\CorrectionQuiz;
use App\Entity\QuestionQuiz;
use App\Entity\Quiz;
use App\Entity\ReponseUserQuiz;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReponseUserQuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('est_correcte')
            ->add('temps_reponse')
            ->add('date_reponse')
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'id',
            ])
            ->add('quiz', EntityType::class, [
                'class' => Quiz::class,
                'choice_label' => 'id',
            ])
            ->add('questionQuiz', EntityType::class, [
                'class' => QuestionQuiz::class,
                'choice_label' => 'id',
            ])
            ->add('correctionQuiz', EntityType::class, [
                'class' => CorrectionQuiz::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReponseUserQuiz::class,
        ]);
    }
}
