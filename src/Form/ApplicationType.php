<?php

/* CREATION D'UNE CLASSE MAISON (dans laquelle on retrouvera des méthodes utiles aux formulaires de l'application) */

namespace App\Form; // On créé un namespace pour notre classe maison

use Symfony\Component\Form\AbstractType;

// Permet de donner un label, attribut ou autre option à un champ de formulaire (input)
class ApplicationType extends AbstractType{ // On l'extends avec AbstractType pour l'utilisation des méthode de formulaire

    /**
     * Permet d'avoir la configuration de base d'un champ de formulaire 
     * (utile notement sur le champs de collection d'image, soit le formulaire qui sera "imbriqué" dans le formulaire principal de création d'annonce)
     *
     * @param string $label
     * @param string $placeholder
     * @param array $options
     * @return array
     */
    protected function getConfig($label, $placeholder, $options = [] ) {
        return array_merge([ // array_merge est une méthode php qui fusionne les tableaux (ici on veut fusionné le tableau avec le label et l'attr avec le tableau $option qui va contenir un tableau vide dans lequel on pourra inporter des options supplémentaires comme un widget...etc)
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder
            ]
        ], $options);
    }
    
}