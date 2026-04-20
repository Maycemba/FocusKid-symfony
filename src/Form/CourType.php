<?php

namespace App\Form;

use App\Entity\Cour;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', null, [
                'label'    => 'Titre du cours',
                'required' => false,
                'attr'     => [
                    'placeholder' => 'Ex: Alphabet et lecture',
                ],
            ])
            ->add('niveau', ChoiceType::class, [
                'label'       => 'Niveau',
                'required'    => false,
                'placeholder' => '-- Choisir un niveau --',
                'choices'     => [
                    '1'    => '1',
                    '2'    => '2',
                    '3'    => '3',
                    '4'    => '4',
                    '5'    => '5',
                    '6ème' => '6ème',
                    '5ème' => '5ème',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'attr'     => [
                    'placeholder' => 'Décrivez le contenu du cours...',
                    'rows'        => 4,
                ],
            ])
            ->add('formateur', null, [
                'label'    => 'Formateur',
                'required' => false,
                'attr'     => [
                    'placeholder' => 'Nom du formateur',
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'label'       => 'Statut',
                'required'    => false,
                'placeholder' => '-- Choisir un statut --',
                'choices'     => [
                    'Brouillon' => 'Brouillon',
                    'Publié'    => 'Publié',
                    'Archive'   => 'Archive',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cour::class,
        ]);
    }
}