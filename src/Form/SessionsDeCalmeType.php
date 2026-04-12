<?php

namespace App\Form;

use App\Entity\SessionsDeCalme;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class SessionsDeCalmeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type_activite', ChoiceType::class, [
    'choices' => [
        'Musique' => 'musique',
        'Coloriage' => 'coloriage',
        'Respiration' => 'respiration',
        'Histoire' => 'histoire',
    ],
])
            ->add('declencheur', ChoiceType::class, [
    'choices' => [
        'Enfant' => 'enfant',
        'Parent' => 'parent',
    ],
])
            ->add('duree_prevue', IntegerType::class)
->add('duree_reelle', IntegerType::class)
            ->add('horodatage', DateTimeType::class, [
    'widget' => 'single_text',
])
            ->add('feedback_enfant', ChoiceType::class, [
    'choices' => [
        1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5,
    ],
])
            ->add('note_parent')
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SessionsDeCalme::class,
        ]);
    }
}
