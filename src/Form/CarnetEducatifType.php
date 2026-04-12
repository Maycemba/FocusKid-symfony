<?php

namespace App\Form;

use App\Entity\CarnetEducatif;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CarnetEducatifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_etude')
            ->add('heure_debut')
            ->add('heure_fin')
            ->add('duree_totale')
            ->add('lieu')
            ->add('matiere')
            ->add('type_activite')
            ->add('niveau_difficulte')
            ->add('niveau_concentration')
            ->add('niveau_agitation')
            ->add('nombre_interruptions')
            ->add('temps_avant_perte_concentration')
            ->add('travaille_seul')
            ->add('demande_aide')
            ->add('niveau_autonomie')
            ->add('travail_termine')
            ->add('difficultes')
            ->add('points_positifs')
            ->add('created_at')
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CarnetEducatif::class,
        ]);
    }
}
