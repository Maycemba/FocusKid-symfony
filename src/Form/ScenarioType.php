<?php

namespace App\Form;

use App\Entity\Scenario;
use App\Entity\Emotion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ScenarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('animation', FileType::class, [
                'label' => 'Animation (fichier vidéo ou GIF)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'accept' => 'video/*,image/gif']
            ])
            ->add('emotion', EntityType::class, [
                'label' => 'Émotion associée',
                'class' => Emotion::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Scenario::class,
        ]);
    }
}