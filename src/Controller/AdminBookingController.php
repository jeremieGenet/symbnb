<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\AdminBookingType;
use App\Form\AdminCommentType;
use App\Repository\BookingRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\PaginationService;

class AdminBookingController extends AbstractController
{
    /**
     * Affiche la page d'administration de toutes les réservations
     * le "requirements={"page": "\d+"}" permet de contraindre le paramètre "{page}" dans le routing en lui donnant une Regex qui dis ici que "page" doit être un nombre ou des nombres.
     * On initialise à 1 la variable $page (utilisée dans le routing) qui va nous servir lors de la pagination des annonces. "page" est par défaut = 1 soit notre page de départ
     * 
     * @Route("/admin/bookings/{page}", name="admin_bookings_index", requirements={"page": "\d+"})
     */
    public function index(BookingRepository $repo, $page = 1, PaginationService $pagination) // On oublie pas "d'injecter" le repository des réservations (pour pouvoir communique avec la bdd)
    {
        // CONFIGURATION DE LA PAGINATION (utilisation de la classe PaginationService)
        $pagination->setEntityClass(Booking::class) // $pagination reçoit la classe Booking.php
                   ->setCurrentPage($page) // $pagination reçoit la page actuelle (dans le routing, et initialisé dans les param de la fonction index)
                   ->setLimit(10); // On définit la limite du nb de réservations à afficher par page

        /*
        dump($pagination->getData()); // Affiche les réservations (dans les limitations données par la méthode getData(), soit ici une limite de 10)
        die();
        */

        return $this->render('admin/bookings/admin_bookings.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet de modifier une réservation en mode admin
     * 
     * @Route("/admin/booking/{id}/edit", name="admin_booking_edit")
     *
     * @param Booking $booking
     * @return Response
     */
    public function edit(Booking $booking, Request $request, ObjectManager $manager){
        // Création du formulaire de type AdminBookingType.php qu'on relie avec notre "$booking" récupéré en paramètre
        $form = $this->createForm(AdminBookingType::class, $booking); 

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // Recalcule de montant en fonction des différence qu'il peut y avoir après modification et soumission du formulaire
            $booking->setAmount($booking->getAd()->getPrice() * $booking->getDuration()); // On récup le prix de l'annonce de la résa Multiplié à la durée de réservation

            $manager->persist($booking); // On fait persister la réservation modifiée
            $manager->flush(); // On envoie la requête à la bdd

            // On prévient l'utilisateur avec un message Flash de la modif de la réservation
            $this->addFlash(
                'success',
                "La réservation <strong>{$booking->getId()}</strong> à bien été modifiée !"
            );

            return $this->redirectToRoute("admin_bookings_index"); // On redirige vers la page d'administration des annonces

        }
        
        return $this->render('admin/bookings/edit_booking.html.twig', [
            'booking' => $booking,
            'form' => $form->createView() // On stock dans 'form' la vue du formulaire
        ]);
    }

    /**
     * Permet de supprimer une réservation en mode administrateur
     * 
     * @Route("/admin/booking/{id}/delete", name="admin_booking_delete")
     *
     * @param Booking $booking
     * @param ObjectManager $manager
     * @return Response
     */
    public function delete(Booking $booking, ObjectManager $manager){
        
        $manager->remove($booking); // On supprime la réservation
        $manager->flush(); // On envoie à la bdd

        $this->addFlash(
            'success',
            "La réservation <strong>{$booking->getId()}</strong> à bien été supprimée !"
        );
        
        return $this->redirectToRoute('admin_bookings_index'); // On redirige vers la page de modération des réservations
    }
    

}
