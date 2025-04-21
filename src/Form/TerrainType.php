<?php
namespace App\Form;

use App\Entity\Terrain;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class TerrainType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom du terrain'])
            ->add('adresse', TextType::class, ['label' => 'Adresse'])
            ->add('surface', NumberType::class, ['label' => 'Surface (m²)'])
            ->add('prix', NumberType::class, ['label' => 'Prix (€)'])
            ->add('disponible', CheckboxType::class, [
                'label' => 'Disponible',
                'required' => false,
            ])
            /*->add('proprietaire', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email', // Afficher l'email du propriétaire
                'label' => 'Propriétaire',
            ])*/
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image du terrain',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer l\'image',
                'download_uri' => false,
                'image_uri' => true,
                'asset_helper' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Terrain::class,
        ]);
    }
}