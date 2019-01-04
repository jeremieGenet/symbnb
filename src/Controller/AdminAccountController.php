<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminAccountController extends AbstractController
{
    /**
     * Permet d'afficher et de gérer le formulaire de connexion d'administration
     * 
     * @Route("/admin/login", name="admin_account_login")
     */
    public function login(AuthenticationUtils $utils)
    {
        // getLastAuthenticationError() est un outil de gestion d'erreur d'authentification
        $error = $utils->getLastAuthenticationError(); // méthode qui renvoie null s'il n'y a pas d'erreur, sinon renverra un message d'erreur
        //dump($error); // Affiche l'erreur d'authentification, ou null s'il n'y en a pas
        $username = $utils->getLastUsername(); // On récupère le username de l'utilisateur (son adresse email), dans le but de ne pas avoir à le retaper dans le formulaire

        return $this->render('admin/account/login.html.twig', [
            'hasError' => $error !== null, // on crée une variable twig 'hasError' dans laquel on va stocker un booléen en disant est-ce que $error est différent de nul (puis dans le fichier login.html du dossier admin, on va créer une condition)
            'username' => $username // dernier nom d'utilisateur utilisé
        ]);
    }


    /**
     * Permet de se déconnecter
     * 
     * @Route("/admin/logout", name="admin_account_logout")
     *
     * @return void
     */
    public function logOut(){
         // C'est symfony qui va gérer le traitement de la déconnexion utilisateur (grace à la configuration ('logout' et son path) dans le fichier "security.yaml")
    }
}
