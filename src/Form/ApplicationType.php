<?php

/* CREATION D'UNE CLASSE MAISON (dans laquelle on retrouvera des méthodes utiles aux formulaires de l'application */

namespace App\Form; // On créé un namespace pour notre classe maison

use Symfony\Component\Form\AbstractType;

class ApplicationType extends AbstractType{ // On l'extends avec AbstractType pour l'utilisation des méthode de formulaire

    /**
     * Permet d'avoir la configuration de base d'un champ de formulaire (le champs de collection d'image, soit le formulaire qui sera "imbriqué" dans le formulaire principal de création d'annonce)
     *
     * @param string $label
     * @param string $placeholder
     * @param array $options
     * @return array
     */
    protected function getConfig($label, $placeholder, $options = [] ) {
        return array_merge([ // array_merge est une méthode php qui fusionne les tableaux (ici on veut fusionné le tableau avec le label et l'attr avec le tableau $option qui va contenir )
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder
            ]
        ], $options);
    }
    
}