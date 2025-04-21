<?php
// src/Form/ReservationType.php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Terrain;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text'
            ])
            ->add('dateFin', DateTimeType::class, [
                'widget' => 'single_text'
            ])
        ;

        if (!$options['terrain_locked']) {
            $builder->add('terrain', EntityType::class, [
                'class' => Terrain::class,
                'choice_label' => 'name',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'terrain_locked' => false,
        ]);
    }
}