<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Persistence\ObjectManager;

class AdminAdController extends AbstractController
{
    /**
     * Affiche la page des annonces en mode administration
     * 
     * @Route("/admin/ad", name="admin_ads_index")
     */
    public function index(AdRepository $repo) // On oublie pas "d'injecter" le repository de l'annonce (pour pouvoir communique avec la bdd)
    {
        return $this->render('admin/ad/admin_ads.html.twig', [
            'ads' => $repo->findAll() // On va chercher nos annonces (dans la bdd) grace au repository auquel on applique la méthode findAll() de symfony
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
