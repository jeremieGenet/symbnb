<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\Common\Collections\Collection; // Utile à la fonction initializeSlug()
use Symfony\Component\Validator\Constraints as Assert; // Utile pour ajouter des contraintes sur les attributs de notre classe (dans le but d'une validation "controlée" du formulaire d'annonce)

/**
 * Classe des Annonces (Le "@ORM\HasLifecycleCallbacks" prévient doctrine qu'il y a des fonctions liées au cycle de vie)
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


    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->no = new ArrayCollection();
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
}
