<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\StatsService;

class AdminDashboardController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_dashboard")
     */
    public function index(ObjectManager $manager, StatsService $statsService) // On importe par injection de dépendance StatsService.php qui contient les requetes DQL
    {
        // On récupère les statistiques (le nb total d'utilisateurs, annonces, réservations et commentaires) via la méthode getStats() de la classe StatsService.php
        $stats = $statsService->getStats(); // Renvoie un tableau

        $bestAds = $statsService->getBestAds(); // Récup des meilleurs annonces (les mieux notées)

        $worstAds = $statsService->getWorstAds(); // Récup des pires annonces (les moins bien notées)

        
        return $this->render('admin/dashboard/dashboard.html.twig', [
            'stats' => $stats, 
            'bestAds' => $bestAds,
            'worstAds' => $worstAds
        ]);
    }
}
