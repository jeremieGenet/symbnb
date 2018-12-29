<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Form\BookingType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookingController extends AbstractController
{
    /**
     * Permet de créer une réservation (formulaire)
     * 
     * @Route("/ad/{slug}/book", name="booking_create")
     * @IsGranted("ROLE_USER")
     */
    public function book(Ad $ad, Request $request, ObjectManager $manager)
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);

        $form->handleRequest($request); // On gère la requête du formulaire

        if($form->isSubmitted() && $form->isValid()){
            $user = $this->getUser(); // On récupère l'utilisateur qui est actuellement connecté

            $booking->setBooker($user) // On lie notre réservation à l'utilisateur (qui est le reservateur, c'est l'utilisateur connecté)
                    ->setAd($ad); // On lie notre réservation à l'annonce (quelle annonce est réservé, c'est l'annonce reçue en paramConverter dans la fonction)
                    // La date de réservation (createdAt) et le montant (amount) sont calculés directement dans l'entité Booking.php     

                // Si les dates ne sont pas dispo, message d'erreur 
                if(!$booking->isBookableDates()){
                    // addFlash() sert à notifier l'utilisateur de ce qui à été fait (param 1 = type de message, param 2 = message)
                    $this->addFlash(
                        'warning',
                        "Les dates que vous avez choisies ne peuvent être réservées : elle sont déjà prises !"
                    );
                // Sinon les dates sont ok, alors enregistement et redirection
                }else{
                    $manager->persist($booking);
                    $manager->flush();

                    // Redirige vers la page qui affiche une réservation (showBooking.html.twig), soit l'url : "/booking/{id}" ou "/booking/{id}?booking_success" si la réservation est validée par l'utilisateur
                    return $this->redirectToRoute('booking_show', ['id' => $booking->getId(),
                    'booking_success' => true]); // La variable "booking_success" sera en fait une variable "get" qui permettra lors de la validation de réservation d'afficher un message de validation
                }
        }

        return $this->render('booking/book.html.twig', [
            'ad' => $ad, // On met en paramètre twig la variable $ad (qui contient notre annonce)
            'form' => $form->createView() // On met en paramètre twig la variable $form (qui contient notre formulaire)
        ]);
    }

    /**
     * Permet d'afficher la page d'une réservation
     * 
     * @Route("/booking/{id}", name="booking_show")
     *
     * @param Booking $booking
     * @return Response
     */
    public function showBooking(Booking $booking){
        return $this->render('booking/showBooking.html.twig', [
            'booking' => $booking // On met en paramètre twig la variable $booking (qui contient notre réservation)
        ]);
    }

}
