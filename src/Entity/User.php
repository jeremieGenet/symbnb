<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface; // Interface ajoutée à notre classe User pour faire fonctionner le systeme d'encodage (possède des fonction utiles au fonctionnement de symfony)

use Symfony\Component\Validator\Constraints as Assert; // Utile pour ajouter des contraintes sur les attributs de notre classe (dans le but d'une validation "controlée" du formulaire d'annonce)
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * CLASSE UTILISATEUR
 * Le "@ORM\HasLifecycleCallbacks" (prévient doctrine qu'il y a des fonctions liées au cycle de vie), est utilisé dans la fonction initializeSlug() plus bas
 * Le "UniqueEntity" permet de s'assurer de la valeur "unique" d'une entité, ici on s'assure que l'utilisateur ne s'inscrit pas avec une adresse mail déjà existante dans la bdd
 * 
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(
 *  fields={"email"},
 *  message="Un autre utilisateur possède déjà cette adresse email , merci de la modifiée"
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Ici exemple d'utilisation des contraintes (@Assert\NotBlank() permet de s'assurer que le firstName ne soit pas vide)
     * 
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Vous devez renseigner votre prénom !")
     */
    private $firstName;

    /**
     * 
     * 
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Vous devez renseigner votre nom de famille !")
     */
    private $lastName;

    /**
     * Ici exemple d'utilisation des contraintes (@Assert\Email() permet de s'assurer que la validité d'un email)
     * 
     * 
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message="Veuillez renseigner un email valide !")
     */
    private $email;

    /**
     * AVATAR DE l'utilisateur
     * 
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url(message="Veuillez donner une URL valide pour votre avatar !")
     */
    private $picture;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     */
    private $hash;

    
    /**
     * ATTRIBUT CONFIRMATION DE PASSWORD (ajouté à la main)
     * ATTENTION pas de données "@Orm\Column" pour cet attribut, car il relie l'attribut à un champ de la bdd, ce que l'on ne veut pas ici
     * ("@Assert\EqualTo(...)" permet de s'assurer que la confirmation de password est égal au password ($hash ici) )
     *
     * @Assert\EqualTo(propertyPath="hash",message="Vous n'avez pas correctement confirmé votre mot de passe !")
     */
    public $passwordConfirm;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     */
    private $introduction;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * Représente un tableau d'annonces de l'utilisateur (il peut il n'y en avoir qu'une !)
     * Cette propriété est lié à l'entité Ad.php
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Ad", mappedBy="author")
     */
    private $ads;

    public function getFullName(){
        return "{$this->firstName} {$this->lastName}";
    }

    /**
     * Permet d'initialiser le slug (firstName concaténé au lastName, et formaté en URL), si celui-ci est vide
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
            $this->slug = $slugify->slugify($this->firstName . ' ' . $this->lastName); // La méthode slugify() permet de formater (ici le titre de l'annonce) en un format URL
        }
    }

    public function __construct()
    {
        $this->ads = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|Ad[]
     */
    public function getAds(): Collection
    {
        return $this->ads;
    }

    public function addAd(Ad $ad): self
    {
        if (!$this->ads->contains($ad)) {
            $this->ads[] = $ad;
            $ad->setAuthor($this);
        }

        return $this;
    }

    public function removeAd(Ad $ad): self
    {
        if ($this->ads->contains($ad)) {
            $this->ads->removeElement($ad);
            // set the owning side to null (unless already changed)
            if ($ad->getAuthor() === $this) {
                $ad->setAuthor(null);
            }
        }

        return $this;
    }

    /***** 5 FONCTIONS QUI PROVIENNENT DE L'INTERFACE UserInterface (quand on implémente une interface on est obligé d'inclure les méthodes de celle-ci dans la classe *****/
    
    public function getRoles(){
        return ['ROLE_USER'];
    }

    // Fonction qui retourne le password (dans notre cas il s'appelle $hash)
    public function getPassword(){
        return $this->hash;
    }

    public function getSalt(){
        // Fonction inutile dans notre cas car le sel d'encodage est déja présent dans l'algorithme bcrypt défini dans le fichier security.yaml
    }

    // Fonction qui retourne ce que l'utilisateur va utiliser pour se connecter
    public function getUsername(){
        return $this->email;
    }

    public function eraseCredentials(){
        // Utile si on devait stocker en dur le password utilisateur, ou d'autre info sensible (mais pas dans notre cas et c'est mieux comme cela)
    }
    
}
