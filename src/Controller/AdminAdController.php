<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use App\Service\PaginationService;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAdController extends AbstractController
{
    /**
     * Affiche la page des annonces en mode administration
     * le "requirements={"page": "\d+"}" permet de contraindre le paramètre "{page}" dans le routing en lui donnant une Regex qui dis ici que "page" doit être un nombre ou des nombres.
     * On initialise à 1 la variable $page (utilisée dans le routing) qui va nous servir lors de la pagination des annonces. "page" est par défaut = 1 soit notre page de départ
     * 
     * @Route("/admin/ad/{page}", name="admin_ads_index", requirements={"page": "\d+"})
     */
    public function index(AdRepository $repo, $page = 1, PaginationService $pagination) // On oublie pas "d'injecter" le repository des annonces (pour pouvoir communique avec la bdd)
    {
        // CONFIGURATION DE LA PAGINATION (utilisation de la classe PaginationService)
        $pagination->setEntityClass(Ad::class) // $pagination reçoit la classe Ad.php
                   ->setCurrentPage($page) // $pagination reçoit la page actuelle (dans le routing, et initialisé dans les param de la fonction index)
                   ->setLimit(10); // On définit la limite du nb d'annonce à afficher par page

        /*
        dump($pagination->getData()); // Affiche les annonces (dans les limitations données par la méthode getData(), soit ici une limite de 10)
        die();
        */

        return $this->render('admin/ad/admin_ads.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition
     * 
     * @Route("/admin/ads/{id}/edit", name="admin_ads_edit")
     *
     * @param Ad $ad
     * @return Response
     */
    public function edit(Ad $ad, Request $request, ObjectManager $manager){
        $form = $this->createForm(AnnonceType::class, $ad); // Création d'un formulaire de type AdType qu'on relie avec notre "$ad" récupérée en paramètre

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $manager->persist($ad); // On fait persister l'annonce
            $manager->flush(); // On envoie la requête

            // On prévient l'utilisateur avec un message Flash de la modif de l'annonce
            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> à bien été enregistrée !"
            );

        }

        return $this->render('admin/ad/edit.html.twig', [
            'ad' => $ad,
            'form' => $form->createView() // On stock dans 'form' la vue du formulaire
        ]);
    }

    /**
     * Permet de supprimer une annonce en mode administrateur
     * 
     * @Route("/admin/ads/{id}/delete", name="admin_ads_delete")
     *
     * @param Ad $ad
     * @param ObjectManager $manager
     * @return Response
     */
    public function delete(Ad $ad, ObjectManager $manager){
        // Si l'annonce possède des réservations 
        if(count($ad->getBookings()) > 0){
            $this->addFlash(
                'warning',
                "L'annonce <strong>{$ad->getTitle()}</strong> possède des réservations, vous ne pouvez dans pas la supprimer !"
            );
        }else{
            $manager->remove($ad); // On supprime l'annonce
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> à bien été supprimée !"
            );
        }
        
        return $this->redirectToRoute('admin_ads_index');
    }
}
