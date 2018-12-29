<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

// PERMET DE TRANSFORMER UNE DATE DE TYPE FR A UNE DATE DE TYPE DATETIME ET VISE-VERSA
// Notre classe implémente l'interface DataTransformerInterface, ce qui sous-entend que les fonctions de celle-ci sont obligatoire à notre classe
class FrenchToDateTimeTransformer implements DataTransformerInterface{

    // Transforme une date de type DateTime au format français
    public function transform($date){
        // Si $date est null on retourne rien du tout
        if($date === null){
            return '';
        }
        return $date->format('d/m/Y'); // Retourne un date au format fr
    }

    // Retransforme une date au format fr à celui de type DateTime
    public function reverseTransform($frenchDate){
        // $frenchDate = 20/09/2018 (exemple d'une date au format fr)
        if($frenchDate === null){
            // Exception (jete une exception si $frenchDate est null)
            throw new TransformationFailedException("Vous devez fournir une date ! Nous somme dans FrenchToDateTimeTransform.php !"); // Message invisible à l'utilisateur
        }

        $date = \DateTime::createFromFormat('d/m/Y', $frenchDate); // la fonction createFormat() retourne false si elle n'arrive pas à formater la date passée en paramètre

        if($date === false){
            // Exception (jete une exception si $date vaut false, car createFormFormat() retourne false si elle n'arrive pas à formater la date passée en paramètre)
            throw new TransformationFailedException("Le format de la date n'est pas le bon ! Nous somme dans FrenchToDateTimeTransform.php !");
        }

        return $date;
    }

}