<?php

namespace App\Form;

use App\Entity\Jeu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class JeuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Quiz' => 'quiz',
                    'Autre' => 'autre',
                ],
            ])
            ->add('niveau', ChoiceType::class, [
                'choices'  => [
                    'Facile' => 'Facile',
                    'Moyen' => 'Moyen',
                    'Elevé' => 'Eleve',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Jeu::class,
        ]);
    }
}
