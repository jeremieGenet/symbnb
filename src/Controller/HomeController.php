<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Repository\AdRepository;
use App\Repository\UserRepository;

class HomeController extends Controller{

    // Fonction public qui va correspondre à notre affichage de la page d'accueil
    /**
     * @Route("/", name="homepage")
     */
    public function home(AdRepository $adRepo, UserRepository $userRepo){ // On injecte les repository des annonnces et utilisateurs pour utiliser les méthodes findBestAds() et findBestUsers()
        
        return $this->render(
            'home.html.twig',[
                // bestads représente donc un tableau de 3 tableaux des meilleurs annonce et leur note moyenne
                'bestAds' => $adRepo->findBestAds(3), // Le paramètre ici permet de modifier le nombre d'annonces stars de la page Home
                'bestUsers' => $userRepo->findBestUsers(2)
            ]
        );

    }


}

?>