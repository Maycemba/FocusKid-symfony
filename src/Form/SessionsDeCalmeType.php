<?php

namespace App\Form;

use App\Entity\SessionsDeCalme;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionsDeCalmeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type_activite')
            ->add('declencheur')
            ->add('duree_prevue')
            ->add('duree_reelle')
            ->add('horodatage')
            ->add('feedback_enfant')
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
