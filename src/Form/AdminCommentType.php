<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

// Formulaire de modification de commentaire en mode administrateur (qui ne possède que le champ de contenu du commentaire)
class AdminCommentType extends ApplicationType // RAPPEL: ApplicationType (permet la config "maison" des champs de formulaire) s'extends lui-même de AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, $this->getConfig("Contenu de commentaire : ", "Correction du contenu du commentaire..."))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
