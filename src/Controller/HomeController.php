<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller{

    // Fonction public qui va correspondre à notre affichage de la page d'accueil
    /**
     * @Route("/", name="homepage")
     */
    public function home(){
        
        return $this->render(
            'home.html.twig'
        );

    }


}

?>