<?php

namespace App\Repository;

use App\Entity\Ad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Ad|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ad|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ad[]    findAll()
 * @method Ad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Ad::class);
    }

    /**
     * Permet de récupérer les annonces et leurs note (par un trie des meilleurs notes) via une requête DQL
     *
     * @param number $limit
     * @return table qui contiendra les annonces, et leurs notes triées par moyenne des notes de la plus haute à la plus basse
     */
    public function findBestAds($limit){
        // Pas besoin de préciser d'entité, nous sommes dans le AdRepository donc l'entité est forcément celui des annonces
        return $this->createQueryBuilder('a') // on défini "a" comme alias de l'entité des annonces (Ad.php)
                    // on fait une jointure entre l'annonce et les commentaires, et "c" est maintenant la liaison entre commentaires et annonce
                    ->join('a.comments', 'c')
                    // on selectionne l'entité Ad.php que l'on nomme "annonce", et on selectionne la fonction d'agrégation AVG qui calcul la moyenne
                    // de "c.rating" qui est la note du commentaire de cette annonce auquelle que l'on renomme avgRatings (c'est le nom avgRatings que l'on verra dans un dump())
                    // et l'on calcule le nb de commentaires de ces utilisateurs (pour que les annonces stars de notre site aient un minimum de commentaires config dans le "having" plus bas)
                    ->select('a as annonce, AVG(c.rating) as avgRatings, COUNT(c) as sumComments') 
                    // on groupe par annonce
                    ->groupBy('a')
                    // le nombre de commentaires doit être supérieur ou égal à 2 commentaires 
                    ->having('sumComments >= 2') 
                    // on le trie par moyenne des notes la plus haute à la plus basse
                    ->orderBy('avgRatings', 'DESC')
                    // on donne la possibilité de modifié le nombre de résultats (via le paramètre de la fonction)
                    ->setMaxResults($limit)
                    // on récup la requête
                    ->getQuery()
                    // on récup les résultats
                    ->getResult();
    }

//    /**
//     * @return Ad[] Returns an array of Ad objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ad
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
