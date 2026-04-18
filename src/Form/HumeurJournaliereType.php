<?php

namespace App\Form;

use App\Entity\Emotion;
use App\Entity\HumeurJournaliere;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HumeurJournaliereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('emotion', EntityType::class, [
                'class' => Emotion::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez une émotion',
                'required' => true,
                'attr' => ['class' => 'form-select'],
                'label' => 'Émotion'
            ])
            ->add('dateHeure', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'label' => 'Date et heure'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HumeurJournaliere::class,
        ]);
    }
}