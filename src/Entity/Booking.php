<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert; // Utile pour ajouter des contraintes sur les attributs de notre classe (dans le but d'une validation "controlée" du formulaire de réservation)

/**
 * CLASSE QUI GERE LES RESERVATIONS (Relation avec User.php et avec Ad.php)
 * le "@ORM\HasLifecycleCallbacks()" permet dire à doctrine que cette classe doit gérer son cycle de vie ,
 * (cad qu'il y a des fonctions à différent moment de son cycle de vie), utile ici à la fonction prePersist(),
 *  à laquel il faut ajouter l'annotation "@ORM\PrePersit" pour que cette fonction soit appellée à chaque fois qu'une réservation soit créé.
 * 
 * @ORM\Entity(repositoryClass="App\Repository\BookingRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Booking
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $booker;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ad", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ad;

    /**
     * le "@Assert\GreaterThan("today")" signifie que la date de départ sera obligatoirement plus grande qu'aujourd'hui (on ne peut réservé une date antérieur à celle du jour !)
     * (Rappel: "GreaterThan("today")" se base sur une date de type DateTime, donc "today" est un argument valable, mais on aurait pu mettre un nombre ou tout autre argument recevable par l'objet DateTime)
     * 
     * @ORM\Column(type="datetime")
     * @Assert\Date(message="Attention, la date d'arrivée doit être au bon format !")
     * @Assert\GreaterThan("today", message="La date d'arrivée doit être supérieur à la date d'aujourd'hui !")
     */
    private $startDate;

    /**
     * Le "@Assert\GreaterThan(propertyPath="startDate")" signifie que la date de fin de réservation doit être supérieur à celle de d'arrivée ($startDate)
     * 
     * @ORM\Column(type="datetime")
     * @Assert\Date(message="Attention, la date de fin doit être au bon format !")
     * @Assert\GreaterThan(propertyPath="startDate", message="La date de départ doit être supérieur à celle de d'arrivée !")
     */
    private $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;


    /**
     * Fonction manuelle qui définit la date de création de la réservation et son montant
     * Callback qui sera appellée à chaque fois qu'une reservation sera créé 
     * (voir annotation "@ORM\HasLifecycleCallbacks()" de la classe Booking.php) grace à l'annotation "@ORM\PrePersist"
     *
     * @ORM\PrePersist
     * 
     */ 
    public function prePersist(){
        if(empty($this->createdAt)){
            // On définit la date de création de la réservation
            $this->createdAt = new \DateTime(); 
        }

        if(empty($this->amount)){
            // On définit le montant de la réservation
            $this->amount = $this->ad->getPrice() * $this->getDuration(); // Prix de l'annonce * le nombre de jour
        }
    }

    public function isBookableDates(){
        // 1) On récupère l'ensemble des journées pour lesquels cette annonce n'est pas disponible (sous forme d'un tableau qui contient des objets DateTime)
        $notAvailableDays = $this->ad->getNotAvailableDays(); // On utilise la fonction que l'on a créé dans BookingController.php (getNotAvailableDays())
        // 2) On récupère l'ensemble des journées de ma réservation en cours (sous forme d'un tableau qui contient des objets DateTime)
        $bookingDays = $this->getDays(); // On stock les jours de ma réservation (les jours de la durée de résa)

        // Fonction qui sera l'argument1 de la fonction array_map() plus bas
        $formatDay = function($day){  
            return $day->format('Y-m-d');
        };

        // On transforme le tableau "bookingDays" (tableau de type DateTime) en chaine de caractères de type Y-m-d soit un format date
        $days = array_map($formatDay, $bookingDays);

        // On transforme le tableau "notAvailableDays" (tableau de type DateTime) en chaine de caractères de type Y-m-d soit un format date
        $notAvailableDays = array_map($formatDay, $notAvailableDays); // $notAvailableDays représente les jours pour lesquels cette annonce n'est pas dispo

        // On boucle sur les jours de la réservation ($days), et pour chaque journée on regarde si elle est présente parmi les journées non disponibles,
        // si c'est le cas, alors cela retourne false, sinon retourne true
        foreach($days as $day){
            if(array_search($day, $notAvailableDays) !== false) return false;
        }

        return true;
    }

    /**
     * Permet de récupérer un tableau des journées qui correspondent à ma réservation
     *
     * @return array Un tableau d'objet DateTime représentant les jours de la réservation
     */
    public function getDays(){
        $resultat = range(
            $this->startDate->getTimestamp(),
            $this->endDate->getTimestamp(),
            24 * 60 * 60
        );

        $days = array_map(function($dayTimestamp){
            return new \DateTime(date('Y-m-d', $dayTimestamp));
        }, $resultat);

        return $days; // $days représente un tableau de jours de ma réservation (les jours de la durée de résa)
    }

    /* Fonction manuelle qui calcul la duréé d'un annonce (soit la différence entre la date de début (startDate) et la date de fin (endDate) */
    public function getDuration(){
        $diff = $this->endDate->diff($this->startDate); // la méthode diff() provient des objet de type DateTime et permet de faire la différence entre 2 dates et renvoie un objet DateInterval
        return $diff->days; // Retour la différence en jour (soit un nombre)
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooker(): ?User
    {
        return $this->booker;
    }

    public function setBooker(?User $booker): self
    {
        $this->booker = $booker;

        return $this;
    }

    public function getAd(): ?Ad
    {
        return $this->ad;
    }

    public function setAd(?Ad $ad): self
    {
        $this->ad = $ad;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreatedAt(\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
