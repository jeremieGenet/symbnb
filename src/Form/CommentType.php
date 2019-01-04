<?php

namespace App\Form;

use App\Entity\Comment;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

// Classe qui représente le formulaire de notation à remplir après le séjour
class CommentType extends ApplicationType // RAPPEL: ApplicationType (permet la config "maison" des champs de formulaire) s'extends lui-même de AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Configuration du champ de notation de l'annonce auquel on ajoute un label et un placeHolder, et en plus une option qui cape la note entre 0 et 5
            ->add('rating', IntegerType::class, $this->getConfig("Note sur 5", "Veuillez indiquer votre note de 0 à 5", [
                'attr' => [
                    'min' => 0,
                    'max' => 5,
                    'step' => 1
                ]
            ]))
            ->add('content', TextareaType::class, $this->getConfig("Votre avis / témoignage", "N'hésitez pas à être très précis, cela aidera nos futurs voyageurs !"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
