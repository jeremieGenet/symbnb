<?php

namespace App\Service;

use Doctrine\Common\Persistence\ObjectManager;


class StatsService{
    private $manager; // Manager de doctrine (passé dans le constructeur de la classe)

    // A la construction de l'objet, on injecte le manager de doctrine (permet notamment de faire des requêtes DQL)
    public function __construct(ObjectManager $manager){
        $this->manager = $manager;
    }

    /**
     * Permet de récupérer le nb total d'utilisateurs, annonces, réservations et commentaires
     *
     * @return tableau
     */
    public function getStats(){
        $users =  $this->getUsersCount(); // Utilisation de la méthode getUsersCount() pour récupérer le nombre total d'utilisateurs
        //dump($users); // Le dump va afficher le nombre d'utilisateurs de type number
        $ads = $this->getAdsCount(); // Utilisation de la méthode getAdsCount() pour récupérer le nombre total d'utilisateurs
        $bookings = $this->getBookingsCount();  
        $comments = $this->getCommentsCount();

        // La fonction compact() de php permet de créer un tableau associatif en nomant les clés de la même façon que leurs valeurs
        return compact('users', 'ads', 'bookings', 'comments');
    }

    /**
     * Permet de récupérer les meilleurs annonces (les mieux notées)
     *
     * @return tableau
     */
    public function getBestAds(){
        /*      EXPLICATION DE LA REQUETE DQL QUI SUIT (requête doctrine)
        
            SELECT...: Selection (via la fonction d'agrégation doctrine AVG qui calcul la moyenne d'une note) d'un commentaire "c" (c que l'on va initialiser un peu plus bas) 
                comme (as) note (cad que cette moyenne sera nommé "note"), Selection aussi du titre de l'annonce "a" ("a" que l'on va initialiser un peu plus bas)
                et Selection de l'id de l'annonce, et Selection du firstName de l'utilisateur "u" ("u" que l'on va initialiser un peu plus bas), et Selection du 
                lastName de l'utilisateur ainsi que de l'image de son avatar 
            FROM... : Toute cette sélection sera faite à partir de l entité Comment.php et on initialise c ici donc comme Comment.php
            JOIN c.ad.a: On fait un jointure sur l annonce du commentaire (c est maintenant un commentaire, et possède l attribut ad qui est en relation avec l annonce),
                et on le nomme a (a représente maintenant l entité Ad.php)
            JOIN a.author u: On fait un jointure sur l auteur de l annonce, et on le nomme u (u va ainsi représenter l entité User.php)
            GROUP BY a: Groupé par annonces
            ORDER BY note DESC: Ordonné par note descendante (donc la premiere note sera la plus haute)
        */
        // REQUETE POUR OBTENIR LES MEILLEURS ANNONCES
        return $this->manager->createQuery(
            'SELECT AVG(c.rating) as note, a.title, a.id, u.firstName, u.lastName, u.picture 
            FROM App\EnTity\Comment c 
            JOIN c.ad a
            JOIN a.author u
            GROUP BY a 
            ORDER BY note DESC' 
        )
        ->setMaxResults(5) // On précise une limite à notre requête de récupération des notes (limite de 5)
        ->getResult(); // On récup le résultat (sous forme d'une entité)
        // dump($bestAds); // Affiche 5 tableaux avec les notes et info que l'on voulait
    }
    
    /**
     * Permet de récupérer les pires annonces (les moinb bien notées)
     *
     * @return tableau
     */
    public function getWorstAds(){
        // REQUETE POUR OBTENIR LES PIRES ANNONCES
        return $this->manager->createQuery(
            'SELECT AVG(c.rating) as note, a.title, a.id, u.firstName, u.lastName, u.picture 
            FROM App\EnTity\Comment c 
            JOIN c.ad a
            JOIN a.author u
            GROUP BY a 
            ORDER BY note ASC' 
        )
        ->setMaxResults(5) // On précise une limite à notre requête de récupération des notes (limite de 5)
        ->getResult(); // On récup le résultat (sous forme d'une entité)
        // dump($worstAds); // Affiche 5 tableaux avec les notes les pires et infos que l'on voulait
    }

    // getResult() récupère les résultats sous forme d'objets Entité (ici des objets de User.php)

    /**
     * Permet de récupérer le nombre total d'utilisateur dans notre bdd (via une requête DQL, doctrine)
     * UTILE DANS NOTRE DASHBOARD (pour les statistiques)
     * createQuery() permet d'écrire une requête DQL (requête Doctrine), sous forme de chaine de caractères
     * getSingleScalarResult() permet d'obtenir le résultat sous la forme d'une variable scalaire simple
     *
     * @return number
     */
    public function getUsersCount(){
        return $this->manager->createQuery('SELECT COUNT(u) FROM App\Entity\User u')->getSingleScalarResult();
    }

    /**
     * Permet de récupérer le nombre total d'annonces dans notre bdd (via une requête DQL, doctrine)
     * UTILE DANS NOTRE DASHBOARD (pour les statistiques)
     *
     * @return number
     */
    public function getAdsCount(){
        return $this->manager->createQuery('SELECT COUNT(a) FROM App\Entity\Ad a')->getSingleScalarResult();  
    }

    /**
     * Permet de récupérer le nombre total de réservations dans notre bdd (via une requête DQL, doctrine)
     * UTILE DANS NOTRE DASHBOARD (pour les statistiques)
     *
     * @return number
     */
    public function getBookingsCount(){
        return $this->manager->createQuery('SELECT COUNT(b) FROM App\Entity\Booking b')->getSingleScalarResult();  
    }

    /**
     * Permet de récupérer le nombre total de commentaires dans notre bdd (via une requête DQL, doctrine)
     * UTILE DANS NOTRE DASHBOARD (pour les statistiques)
     *
     * @return number
     */
    public function getCommentsCount(){
        return $this->manager->createQuery('SELECT COUNT(c) FROM App\Entity\Comment c')->getSingleScalarResult();
    }
}
