<?php

namespace App\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Twig\Environment;

// Gère la pagination du site (classe créé manuellement), cette classe nécessite après instanciation qu'on lui passe l'entité sur laquelle on souhaite travailler
class PaginationService{
    private $entityClass; // Représentera l'entité sur laquel on va chercher les informations avec doctrine (et son manager)
    private $limit = 10; // Représente le nombre d'éléments à afficher
    private $currentPage = 1; // Représente la page actuelle de la pagination
    private $manager; // Représentera le manager de doctrine (à la construction de la classe via le paramConverter)  

    // Création d'un constructeur dans le but qu'à la création de nos objet on ait accès au manager de doctrine
    public function __construct(ObjectManager $manager){
        $this->manager = $manager;
    }

    /*
    public function display(){
        $this->twig->display('admin/partials/pagination.html.twig', [
            'page' => $this->currentPage,
            'nbPages' => $this->getPages(),
            'route' => "admin_ads_index"
        ]);
    }
    */

    /**
     * Retourne le nombre total de pages (total des enregistrements d'une entité / la limite)
     *
     * @return number $pages
     */
    public function getPages(){
        // On crée un message d'erreur pour expliquer l'erreur de ne pas spécifier d'entité dans le controleur
        if(empty($this->entityClass)){
            throw new \Exception("Vous n'avez pas spécifié l'entité sur laquelle nous devons paginer ! Utilisez la méthode setEntityClass() de votre objet PaginationService !");
        } 
        // 1) Connaitre le total des enregistrements de la table (du repository)
        $repo = $this->manager->getRepository($this->entityClass); // On recupère le repository via le manager doctrine dans lequel on indique l'entité que l'on veut récup
        $total = count($repo->findAll()); // On compte toute les enregistement de notre repo

        // 2) Faire la division, l'arroundi et le renvoyer (la fonction ceil() retourne un entier supérieur)
        $pages = ceil($total / $this->limit); // total des enregistrements (de l'entité) / le nombre limite (qui est défini dans la classe à 10)

        // 3) On retourne le nombre de pages total
        return $pages;
    }

    /**
     * Permet de récupérer des données (provenant d'une entité) en partant d'un point ($offset), et dans une certaine limite ($limit qui est un nombre définit)
     *
     * @return object $data
     */
    public function getData(){
        // On crée un message d'erreur pour expliquer l'erreur de ne pas spécifier d'entité dans le controleur
        if(empty($this->entityClass)){
            throw new \Exception("Vous n'avez pas spécifié l'entité sur laquelle nous devons paginer ! Utilisez la méthode setEntityClass() de votre objet PaginationService !");
        } 
        // 1) EXPLICATION DU CALCUL DE l'offset (le nombre de départ de la pagination ($offset) )
        // La variable $offset va gérer la configuration de pagination des pages par 10 annonces (si $limit = 10)
        // 1 * 10 = 10 - 10 soit 0  (si on est sur la page actuelle "1", que l'on multiplie par la limite à laquelle elle se soustrait, soit offset = 0)
        // 2 * 10 = 20 - 10 soit 10 (si on est sur la page actuelle "2", que l'on multiplie par la limite à laquelle elle se soustrait, soit offset = 10 etc...)
        $offset = $this->currentPage * $this->limit - $this->limit;

        // 2) Demander au repository de trouver les éléments
        //METHODE FindBy() qui va nous servir à la pagination des annonces
        //        La méthode findBy() permet de trouver des objets grâce à des critères et avec une limite et possède 4 paramètres :
        //    - param1 un tableau de critères de recherche,
        //    - param2 un tableau de critères pour ordonner les informations,
        //    - param3 un nombre (un nombre d'enregistrements (de données) que l'on veut récupérer),
        //    - param4 un nombre (à partir de ou on veut commencer la sélection)
        $repo = $this->manager->getRepository($this->entityClass); // On sélectionne sur quelle entité doctrine doit travailler (recupérer le repository)
        $data = $repo->findBy([], [], $this->limit, $offset); // On fait la sélection des données grâce à la méthode findBy()

        // 3) Renvoyer les éléments (données) en question
        return $data;
    }
    

    public function setEntityClass($entityClass){
        $this->entityClass = $entityClass;

        return $this;
    }

    public function getEntityClass(){
        return $this->entityClass;
    }

    public function setLimit($limit){
        $this->limit = $limit;

        return $this;
    }

    public function getLimit(){
        return $this->limit;
    }

    public function setCurrentPage($currentPage){
        $this->currentPage = $currentPage;

        return $this;
    }

    public function getCurrentPage(){
        return $this->currentPage;
    }
}


