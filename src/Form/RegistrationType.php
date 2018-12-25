<?php

// FICHIER GENERER après avoir taper dans la console make:form puis le nom du formulaire voulu (RegistrationType ici)

namespace App\Form;

use App\Entity\User;
//use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationType extends ApplicationType // RAPPEL: ApplicationType s'extends lui-même de AbstractType
{
    // La méthode getConfig() provient de la classe ApplicationType dans laquel se trouve mes méthodes faites maison

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, $this->getConfig("Prénom", "Votre prénom..."))
            ->add('lastName', TextType::class, $this->getConfig("Nom", "Votre nom de famille..."))
            ->add('email', EmailType::class, $this->getConfig("Email", "Votre adresse email"))
            ->add('picture', UrlType::class, $this->getConfig("Photo de profil", "URL de votre avatar..."))
            ->add('hash', PasswordType::class, $this->getConfig("Mot de passe", "Choisissez un bon mot de passe !"))
            ->add('passwordConfirm', PasswordType::class, $this->getConfig("Confirmation de mot de passe", "Veuillez confirmer votre mot de passe"))
            ->add('introduction', TextType::class, $this->getConfig("Introduction", "Présentez vous  en quelques mots..."))
            ->add('description', TextType::class, $this->getConfig("Description détaillée", "C'est le moment de vous présenter en détails !"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
