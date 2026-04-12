<?php

namespace App\Form;

use App\Entity\CorrectionQuiz;
use App\Entity\QuestionQuiz;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CorrectionQuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu')
            ->add('est_correcte')
            ->add('questionQuiz', EntityType::class, [
                'class' => QuestionQuiz::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CorrectionQuiz::class,
        ]);
    }
}
