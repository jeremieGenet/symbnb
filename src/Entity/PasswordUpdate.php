<?php

// ATTENTION PasswordUpdate n'est pas une entité, c'est une simple classe (qui n'a rien à voir avec la base de donnée)

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert; // Utile pour ajouter des contraintes sur les attributs de notre classe (dans le but d'une validation "controlée" du formulaire d'annonce)

/**
 *  Classe qui va comporter les attributs et méthodes utiles à notre formulaire de changement de mot de passe
 */
class PasswordUpdate
{
    /**
     *  Ancien password
     */
    private $oldPassword;

    /**
     * Nouveau password
     * 
     * @Assert\Length(min=8, minMessage="Votre mot de passe doit faire au moins de 8 caractères !")
     */
    private $newPassword;

    /**
     * Confirmation de password
     * 
     * @Assert\EqualTo(propertyPath="newPassword", message="Vous n'avez pas correctement confirmé votre nouveau mot de passe")
     */
    private $confirmPassword;

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): self
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }
}
