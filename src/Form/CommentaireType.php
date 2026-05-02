<?php

namespace App\Form;

use App\Entity\Commentaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_seule', DateType::class, [
                'label' => '📅 Date du commentaire',
                'widget' => 'single_text',
                'required' => true,
                'mapped' => false,
                'data' => new \DateTime(),
                'attr' => ['class' => 'form-control form-control-lg rounded-3'],
            ])
            ->add('heure_seule', TimeType::class, [
                'label' => '🕐 Heure du commentaire',
                'widget' => 'single_text',
                'required' => true,
                'mapped' => false,
                'data' => new \DateTime(),
                'attr' => ['class' => 'form-control form-control-lg rounded-3'],
            ])
            ->add('texte_commentaire', TextareaType::class, [
                'label' => '💬 Votre commentaire',
                'required' => true,
                'attr' => [
                    'class' => 'form-control rounded-3',
                    'rows' => 5,
                    'placeholder' => 'Saisissez votre commentaire ici...'
                ],
            ])
            ->add('type_commentaire', ChoiceType::class, [
                'label' => '🏷️ Type de commentaire',
                'required' => true,
                'choices' => [
                    '🔍 Observation' => 'Observation',
                    '⚠️ Problème' => 'Problème',
                    '💡 Suggestion' => 'Suggestion',
                    '✨ Amélioration' => 'Amélioration',
                ],
                'attr' => ['class' => 'form-select form-select-lg rounded-3'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
}