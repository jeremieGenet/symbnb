<?php

namespace App\Form;

use App\Entity\Ad;
use App\Form\ImageType; // Utile à la
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType; // Utile aux types de champs de notre formulaire
use Symfony\Component\Form\Extension\Core\Type\TextType; // Utile aux types de champs de notre formulaire
use Symfony\Component\Form\Extension\Core\Type\MoneyType; // Utile aux types de champs de notre formulaire
use Symfony\Component\Form\Extension\Core\Type\IntegerType; // Utile aux types de champs de notre formulaire
use Symfony\Component\Form\Extension\Core\Type\TextareaType; // Utile aux types de champs de notre formulaire
use Symfony\Component\Form\Extension\Core\Type\CollectionType; // Utile aux types de champs de notre formulaire (le champ de collection d'images)


// CLASSE CREER VIA L'INVITE DE COMMANDE (make:form)
// CLASSE QUI GERE LA CREATION D'UN FORMULAIRE D'ANNONCE (le nom de la classe fini par Type, c'est une convention)
class AnnonceType extends AbstractType{

    /**
     * Permet d'avoir la configuration de base d'un champ de formulaire (le champs de collection d'image, soit le formulaire qui sera "imbriqué" dans le formulaire principal de création d'annonce)
     *
     * @param string $label
     * @param string $placeholder
     * @param array $options
     * @return array
     */
    private function getConfig($label, $placeholder, $options = [] ) {
        return array_merge([ // array_merge est une méthode php qui fusionne les tableaux (ici on veut fusionné le tableau avec le label et l'attr avec le tableau $option qui va contenir )
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder
            ]
        ], $options);
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',
             TextType::class,
              $this->getConfig("Titre", "Tapez un super titre pour votre annonce !")
            )
            ->add('slug',
             TextType::class,
              $this->getConfig("Adresse web", "Tapez l'adresse web (automatique)", ['required' => false]) // required => false est l'option de la fonction getConfig (dans le but que le champs du formulaire "adresse web" ne soit plus requis, obligatoire)
            )
            ->add('coverImage', 
            UrlType::class,
             $this->getConfig("Url de l'image principale", "Donnez l'adresse d'une image qui donne vraiment envie")
            )
            ->add('introduction',
             TextType::class,
              $this->getConfig("Introduction", "Donnez une Description globale de l'annonce")
            )
            ->add('content',
             TextareaType::class,
              $this->getConfig("Description détaillée", "Tapez une description qui donne envie de venir chez vous")
            )
            ->add('price',
             MoneyType::class,
              $this->getConfig("Prix par nuit", "Indiquez le prix que vous voulez pour une nuit")
            )
            ->add('rooms',
             IntegerType::class,
              $this->getConfig("Nombre de chambres", "Le nombre de chambres disponibles")
            )
            // ICI LE BUT VA ETRE DE CREER UN CHAMPS COMPLEX (la collection d'images avec la legende et l'url qui sont rattachés à l'annonce, le carrousel)
            // Pour créer le champs suivant: Invite de commande puis taper make:ImageType puis au nom de l'entity rataché taper: Image
            // Ainsi création d'un fichier dans src/form/ImageType.php qui sera un petit formulaire avec une url et une legende
            // CollectionType() est un type de champs qui permet de passer en paramètre un sous formulaire à la demande (Ici on lui passe le formulaire fraichement créé ImageType)
            // "entry_type" est le type de champ pour chaque élément d'une collection
            // "allow_add" permet de préciser si l'on a le droit d'ajouter de nouveaux éléments (), et "allow_delete permet d'en supprimer (sinon on ne pourrait lors de la modification d'une annoncer retirer des images déja enregistrée)
            ->add('images',
             CollectionType::class, 
            [
                'entry_type' => ImageType::class,
                'allow_add' => true,
                'allow_delete' => true
            ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
        ]);
    }
}
