<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findBestUsers($limit){
        return $this->createQueryBuilder('u') // "u" représente ici l'entité User.php puisqu'on se trouve dans le repository UserRepository.php
                    ->join('u.ads', 'a') // on joint les annonces à l'utilisateur en question, et on nomme la jointure "a"
                    ->join('a.comments', 'c') // on joint les commentaires aux annonces de l'utilisateur en question, et on nomme la jointure "c"
                    ->select('u as user, AVG(c.rating) as avgRatings, COUNT(c) as sumComments') // on selectionne les utilisateurs que l'on nomme "user",
                    // mais aussi AVG(calcul de moyenne) des notes des ces commentaires , que l'on nomme "avgRatings", 
                    // et l'on calcule le nb de commentaires de ces utilisateurs (pour que les utilisateurs stars de notre site aient un minimum de commentaires config dans le "having" plus bas)
                    ->groupBy('u') // on groupe par utilisateur (on aura la moyenne des notes de toutes les annonces pour un utilisateur)
                    ->having('sumComments >= 3') // le nombre de commentaires doit être supérieur ou égal à 3 commentaires 
                    ->orderBy('avgRatings', 'DESC') // par ordre des meilleurs au pires notes de commentaires liés à l'annonce de l'utilisateur
                    ->setMaxResults($limit) // On défini la possibilité de limité ne nombre de résultats
                    ->getQuery() // On récup la requête
                    ->getResult(); // On récup les résultats de la requête
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
