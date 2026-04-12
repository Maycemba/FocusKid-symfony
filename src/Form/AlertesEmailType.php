<?php

namespace App\Form;

use App\Entity\AlertesEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlertesEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enfant_id')
            ->add('type_alerte')
            ->add('date_envoi')
            ->add('email_envoye')
            ->add('session_ids')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AlertesEmail::class,
        ]);
    }
}
