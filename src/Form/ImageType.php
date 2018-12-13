<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Classe qui représente une image (son url, et sa légende) de la collection d'images du formulaire de création d'annonces
 * Classe rattachée dans la classe AnnonceType (qui représente elle, le formulaire de création d'annonces complet)
 * 
 * 
 */
class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url',
            UrlType::class, [
                'attr' => [
                'placeholder' => "URL de l'image"
                ]
            ])
            ->add('caption',
            TextType::class, [
                'attr' => [
                'placeholder' => "Légende de l'image"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
