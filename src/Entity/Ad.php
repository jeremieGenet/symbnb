<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection; // Utile à la fonction initializeSlug()

use Symfony\Component\Validator\Constraints as Assert; // Utile pour ajouter des contraintes sur les attributs de notre classe (dans le but d'une validation "controlée" du formulaire d'annonce)
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Classe des Annonces (Le "@ORM\HasLifecycleCallbacks" prévient doctrine qu'il y a des fonctions liées au cycle de vie), utilisé dans la fonction initializeSlug() plus bas
 * Le "UniqueEntity" permet de s'assurer de la valeur "unique" d'une entité, ici on s'assure que l'attribut "title" n'existera pas lors de la création d'une autre annonce
 * 
 * @ORM\Entity(repositoryClass="App\Repository\AdRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(
 *  fields={"title"},
 *  message="Un autre annonce possède déja ce titre, merci de le modifier"
 * )
 * 
 * 
 */
class Ad
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Ici exemple d'utilisation des contraintes sur le titre de l'annonce (ces contraintes sont prisent en compte en back-end lors de la soumission du formulaire de création d'annonce)
     * 
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 2,
     *      max = 80,
     *      minMessage = "Le titre de l'annonce doit comporter au moins 2 caractères",
     *      maxMessage = "Le titre de l'annonce ne doit pas dépasser les 80 caractères"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="text")
     */
    private $introduction;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $coverImage;

    /**
     * @ORM\Column(type="integer")
     */
    private $rooms;

    /**
     * Le "Assert\Valid() force symfony à valider (symfony ne valide pas par défaut les sous-formulaire, les collections) notre collection d'images (dans notre sous formulaire) 
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="ad", orphanRemoval=true)
     * @Assert\Valid()
     */
    private $images;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * Attribut qui réprésente les réservations (liées à notre annonce en OneToMany)
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Booking", mappedBy="ad")
     */
    private $bookings;


    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->no = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    /**
     * Permet d'initialiser le slug (Titre de l'annonce formaté en URL)
     * 
     * Les @ORM\PrePersist et @ORM\PreUpdate permettent de faire que la fonction sera exécutée avant la persistance de l'entité et avant chaque modification
     * (Ne pas oublier de mettre "@ORM\HasLifecycleCallbacks" à la classe Ad() pour prévenir doctrine qu'il y a des fonctions liées au cycle de vie )
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeSlug(){
        if(empty($this->slug)){
            $slugify = new Slugify(); // Utilisation de la classe Slugify() qui possédent des méthodes de formatage
            $this->slug = $slugify->slugify($this->title); // La méthode slugify() permet de formater (ici le titre de l'annonce) en un format URL
        }
    }

    /**
     * Permet d'obtenir un tableau de jours qui ne sont pas disponibles pour cette annonce
     *
     * @return array Un tableau d'objet DateTime représentant les jours d'occupation
     */
    public function getNotAvailableDays(){
        $notAvailableDays = []; // Création d'un tableau vide dans le but d'y stocker les jours d'indisponibilité de l'annonce (de l'appartement)

        // On boucle sur nos réservations (pour chacune de nos réservations on va...)
        foreach($this->bookings as $booking){
            // CALCUL DES JOURS QUI SE TROUVENT ENTRE LA DATE DE D'ARRIVEE ET DE DEPART
            // Fonction range() permet de créer un tableau qui contient chaque étape existant entre deux nombre. 
            // (argument1= nombre de départ, argument2 = nombre d'arrivée, argument3= nombre d'étape (soit le "step" par lequel on calcul))
            // exemple: $resultat = range(10, 20, 2) alors $resultat = [10, 12, 14, 16, 18, 20] ou $resultat = range(0, 6, 3) alors $resultat = [0, 3, 6]
            $resultat = range( // Rappel: getTimestamp renvoie un jour en millisecondes
                $booking->getStartDate()->getTimestamp(), // Argument1 = la date de départ de la réservation transformée en Timestamp (en secondes)
                $booking->getEndDate()->getTimestamp(), // Argument2 = la date de fin de la réservation transformée en Timestamp (en secondes)
                24 * 60 * 60 // Argument3 = le nombre d'étape (on veut un jour en seconde), donc 24jours * 60min * 60sec pour avoir le nombre de secondes que représente un jour
            );

            // TRANSFORMATION ET FUSION DU TABLEAU "$resultat" (qui contient à ce moment là contient les dates d'indisponiblités, mais sous forme de timestamp)
            // array_map() transforme un tableau en fonction de la fonction passée en paramètre, et en second param sur quel tableau on faire cette transformation (ici le tableau "$resultat")
            // date() retourne une date au format donné par le parm1 fourni par le parm2 qui doit être sous forme timestamp (millisecondes)
            // la fonction en param va retourner un objet de type DateTime au format date('Y-m-d') et qui se base sur le timestamp "$dayTimestamp" reçu en paramètre
            $days = array_map(function($dayTimestamp){ 
                return new \DateTime(date('Y-m-d', $dayTimestamp));
            }, $resultat);
            // $days retourne (après la méthode "array_map()" le contenu de $resultat mais sous forme d'un tableau de type DateTime avec des nombres qui correspondent à des jours,
            // (non plus à des timestamps qui sont des gros chiffres (millisecondes))

            // AJOUT DES DATE D'INDISPONIBILITES A NOTRE TABLEAU "$notAvailableDays" (au fer à mesure, puisqu'on est dans une boucle)
            $notAvailableDays = array_merge($notAvailableDays, $days); // array_merge() permet de fusionner deux tableaux
        }

        return $notAvailableDays;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(string $coverImage): self
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(int $rooms): self
    {
        $this->rooms = $rooms;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setAd($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getAd() === $this) {
                $image->setAd(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getNo(): Collection
    {
        return $this->no;
    }

    public function addNo(Image $no): self
    {
        if (!$this->no->contains($no)) {
            $this->no[] = $no;
            $no->setAd($this);
        }

        return $this;
    }

    public function removeNo(Image $no): self
    {
        if ($this->no->contains($no)) {
            $this->no->removeElement($no);
            // set the owning side to null (unless already changed)
            if ($no->getAd() === $this) {
                $no->setAd(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Booking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setAd($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
            // set the owning side to null (unless already changed)
            if ($booking->getAd() === $this) {
                $booking->setAd(null);
            }
        }

        return $this;
    }
}
