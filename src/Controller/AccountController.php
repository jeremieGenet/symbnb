<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\FormError;


class AccountController extends AbstractController
{
    /**
     * Permet d'afficher et de gérer le formulaire de connexion
     * 
     * @Route("/login", name="account_login")
     * 
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError(); // méthode qui renvoie null s'il n'y a pas d'erreur, sinon renverra un message d'erreur
        //dump($error); // Affiche l'erreur d'authentification, ou null s'il n'y en a pas
        $username = $utils->getLastUsername(); // On récupère le username de l'utilisateur (son adresse email), dans le but de ne pas avoir à le retaper dans le formulaire

        return $this->render('account/login.html.twig', [
            'hasError' => $error !== null, // on crée une variable twig 'hasError' dans laquel on va stocker un booléen en disant est-ce que $error est différent de nul (puis dans le fichier login.html on va créer une condition)
            'username' => $username
        ]);
    }

    /**
     * Permet de se déconnecter
     * 
     * @Route("/logout", name="account_logout")
     *
     * @return void
     */
    public function logOut(){
        // C'est symfony qui va gérer le traitement de la déconnexion utilisateur (grace à la configuration ('logout') dans le fichier "security.yaml")
    }

    /**
     * Permet d'afficher le formulaire d'inscription, puis s'il y a des données dans ce même formulaire alors register() ...
     * ...est à nouveau appellé traité les données (utilisation de Request, ObjectManager et UserPasswordEncoder pour l'encodage du password utilisateur)
     *
     * @Route("/register", name="account_register")
     * 
     * @return Response
     */
    public function register(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder){
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request); // On demande à $form de gérer la requête (lorsque le bouton de validation est appuyé)

        // Condition si notre formulaire est soumis et valid alors ...
        if($form->isSubmitted() && $form->isValid()){
            // RAPPEL: Méthode encodePassword() permet d'encoder un password avec l'algorithme paramètré dans le fichier security.yaml
            $hash = $encoder->encodePassword($user, $user->getHash()); // param1 = l'entité sur laquelle on va encoder (désignée dans le fichier security.yaml), param2 = le password à encoder
            $user->setHash($hash); // On modifie les hash de l'utilisateur avec ce que l'on vient d'encoder (la ligne au dessus)
            
            $manager->persist($user); // On fait persister l'utilisateur
            $manager->flush(); // On envoie la requête

            // On prévient l'utilisateur avec un message Flash de son inscription
            $this->addFlash(
                'success',
                "Votre compte à bien été créé ! Vous pouvez maintenant vous connecter !"
            );

            return $this->redirectToRoute('account_login'); // On se redirige vers la page d'authentification (pour qu'il puisse maintenant se connecter)
        }

        return $this->render('account/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher et de traiter le formulaire de modification de profil
     *
     * @Route("/account/profile", name="account_profile")
     * 
     * @return Response
     */
    public function profile(Request $request, ObjectManager $manager){
        $user = $this->getUser(); // On récupère l'utilisateur qui est connecté (grâce à la méthode getUser() disponible quand un utilisateur est connecté)

        $form = $this->createForm(AccountType::class, $user); // On créé le formulaire à partir des information de AccountType et de l'utilisateur connecté

        $form->handleRequest($request); // On demande à $form de gérer la requête (lorsque le bouton de validation est appuyé)

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($user);
            $manager->flush();

            // On prévient l'utilisateur avec un message Flash de la réussite des modifications
            $this->addFlash(
                'success',
                "Votre profil à bien été modifié ! "
            );
        }

        return $this->render('account/profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier le mot de passe
     * 
     * @Route("/account/password-update", name="account_password")
     *
     * @return Response
     */
    public function updatePassword(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder){
        $passwordUpdate = new PasswordUpdate();

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        $form->handleRequest($request); // On demande à $form de gérer la requête (lorsque le bouton de validation est appuyé)

        if($form->isSubmitted() && $form->isValid()){ // Rappel: isValid() vérifie si nos validations (celle dans la classe PasswordUpdate.php ici, les @Assert...) sont ok
            $user = $this->getUser(); // On récupère l'utilisateur qui est connecté (grâce à la méthode getUser() disponible quand un utilisateur est connecté)
            // 1. Vérification si le password entré dans le formulaire (attribut de la classe PasswordUpdate()) est bien le password associé à l'utilisateur
            // en fait on vérifie si le mot de passe que l'on veut modifié est bien le même que dans la bdd
            // Fonction php password_verify() permet de comparer l'argument1 (un password en clair) à l'argument2 (un password 'hasher') et retourne 'true' si se sont les mêmes 
            // (exemple: password_verify('password', '$2y$13$nbZ/J/rG5woOvUIaxI0/KuN.kCI8gmnpIbrziR.kIcRhSQ4bFfI5.')
            dump($user);
            if(!password_verify($passwordUpdate->getOldPassword(), $user->getHash())){ // Si l'ancien mot de passe est différent de celui qui est entré alors...
                // Gestion de l'erreur
                // Ici on récupère le champ "oldPassword" auquel on applique une méthode qui ajoute un erreur à ce même champs (pour cela il faut intencier la classe FormError qui prend en paramètre un message)
                $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez tapé n'est pas votre mot de passe actuel !"));
                
            }else{
                $newPassword = $passwordUpdate->getNewPassword(); // On récupère le nouveau mot de passe tapé par l'utilisateur dans le formulaire de modification profil
                // encodePassword() est une méthode qui demande 2 paramètres (param1 = l'utilisateur connecté, param2= le password à encoder)
                $hash = $encoder->encodePassword($user, $newPassword); // On 'hash' le nouveau password, qui sera stocké dans $hash

                $user->setHash($hash); // On insére dans l'utilisateur en cours le nouveau mot de passe

                $manager->persist($user); // On fait persister
                $manager->flush(); // On envoie les mofidifications

                // On prévient l'utilisateur avec un message Flash de la réussite des modifications
                $this->addFlash(
                    'success',
                    "Votre mot de passe à bien été modifié ! "
                );

                return $this->redirectToRoute('homepage');
            }
            
            
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView()
        ]);
        
    }

    /**
     * Permet d'afficher le profil de l'utilisateur connecté
     * 
     * @Route("/account", name="account_infoUser")
     *
     * @return Response
     */
    public function myAccount(){
        return $this->render('user/infoUser.html.twig', [
            'user' => $this->getUser()  // Rappel: getUser() nous donne l'utilisateur connecté
        ]);
    }
}
