<?php

namespace App\Form;

use App\Entity\Cour;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class CourType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', null, [
                'label' => 'Titre du cours',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Alphabet et lecture',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le titre est obligatoire.',
                    ]),
                ],
            ])
            ->add('niveau', null, [
                'label' => 'Niveau',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: 1',
                    'inputmode' => 'numeric',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le niveau est obligatoire.',
                    ]),
                    new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Le niveau doit etre un nombre.',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Decrivez le contenu du cours',
                    'maxlength' => 30,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La description est obligatoire.',
                    ]),
                    new Length([
                        'max' => 30,
                        'maxMessage' => 'La description ne doit pas depasser 30 caracteres.',
                    ]),
                ],
            ])
            ->add('formateur', null, [
                'label' => 'Formateur',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Nom du formateur',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le formateur est obligatoire.',
                    ]),
                ],
            ])
            ->add('statut', null, [
                'label' => 'Statut',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Brouillon ou Publie',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le statut est obligatoire.',
                    ]),
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
