<?php

namespace App\Form;

use App\Entity\Booking;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Form\ApplicationType; // CLASSE MAISON (dans laquelle on retrouvera des méthodes utiles aux formulaires de l'application)
use App\Form\DataTransformer\FrenchToDateTimeTransformer; 

class BookingType extends ApplicationType // RAPPEL: ApplicationType (permet la config "maison" des champs de formulaire) s'extends lui-même de AbstractType
{

    // On donne l'attribut $transformer à notre classe qui sera objet de la classe FrenchToDataTimeTransformer.php grace à fonction __construct et l'injection de dépendande de ses paramètres
    // Ainsi $transformer aura accès aux méthodes qui permettent de transformer une date de type DateTime en date au format fr et vis-versa
    private $transformer;

    public function __construct(FrenchToDateTimeTransformer $transformer){
        $this->transformer = $transformer;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // AJOUT DES CHAMPS DU FORMULAIRE de réservation que le réservateur remplira (getConfig() vient de la classe ApplicationType)
        $builder
            // L'option "required => false" permet de ne pas obliger l'utilisateur à remplir le champ de commentaire
            ->add('startDate', TextType::class, $this->getConfig("Date d'arrivée", "La date à laquelle vous comptez arriver"))
            ->add('endDate', TextType::class, $this->getConfig("Date de départ", "La date à laquelle vous quittez les lieux"))
            ->add('comment', TextareaType::class, $this->getConfig("Commentaire","Si vous avez un commentaire, n'hésitez pas à nous en faire part !", ["required" => false]))
        ;

        // GESTION DES FORMATS DES CHAMPS "DATE" DU FORMULAIRE (on lui donne des date au format fr, mais pour l'insertion en bdd il à besoin d'un format objet de type DateTime)
        // Documentation sur les DataTransformers : https://symfony.com/doc/current/form/data_transformers.html
        // On récupère le champs 'startDate' ($builder->get('startDate')), sur lequel on ajoute un transfomer (addModelTransform est une méthode de l'objet builder)...
        // ... et ce "transformer" est l'attribut de notre classe (injection de dépendence du constructeur, soit FrenchToDataTimeTransformer.php)...
        // ... et permet de modifier une date
        $builder->get('startDate')->addModelTransformer($this->transformer); // On gère la transformation de date du champ "startDate" (on lui donne une date au format fr, et il le transforme au format de type DateTime pour qu'il soit compatible lors de l'insertion en bdd)
        $builder->get('endDate')->addModelTransformer($this->transformer); // On gère la transformation de date du champ "endDate"
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
