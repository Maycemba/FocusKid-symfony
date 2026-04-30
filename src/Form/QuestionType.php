<?php

namespace App\Form;

use App\Entity\Jeu;
use App\Entity\Question;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question_text', null, [
                'label' => 'Question',
                'attr' => ['placeholder' => 'Entrez le texte de la question']
            ])
            ->add('option_a', null, [
                'label' => 'Option A',
                'attr' => ['placeholder' => 'Première possibilité']
            ])
            ->add('option_b', null, [
                'label' => 'Option B',
                'attr' => ['placeholder' => 'Deuxième possibilité']
            ])
            ->add('option_c', null, [
                'label' => 'Option C',
                'attr' => ['placeholder' => 'Troisième possibilité']
            ])
            ->add('bonne_reponse', null, [
                'label' => 'Bonne Réponse (A, B ou C)',
                'attr' => ['placeholder' => 'Exemple: A']
            ])
            ->add('jeu', EntityType::class, [
                'class' => Jeu::class,
                'choice_label' => 'titre',
                'label' => 'Jeu associé',
                'disabled' => $options['disable_jeu'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
            'disable_jeu' => false,
        ]);
    }
}
