<?php

namespace App\Form;

use App\Entity\Cour;
use App\Entity\Lecon;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LeconType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre_lecon', null, [
                'label' => 'Titre de la lecon',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Les voyelles',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le titre de la lecon est obligatoire.',
                    ]),
                ],
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ajoutez ici le contenu de la lecon',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le contenu est obligatoire.',
                    ]),
                ],
            ])
            ->add('cour', EntityType::class, [
                'class' => Cour::class,
                'choice_label' => 'titre',
                'disabled' => $options['cour_locked'],
                'placeholder' => $options['cour_locked'] ? false : 'Choisir un cours',
                'label' => 'Cours associe',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le cours associe est obligatoire.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lecon::class,
            'cour_locked' => false,
        ]);
    }
}
