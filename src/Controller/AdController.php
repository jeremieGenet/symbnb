<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AnnonceType; // Utile à la classe createForm()
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\AdRepository; // Utile aux repository dans nos fonctions
use Symfony\Component\HttpFoundation\Response; // Utile à la fonction show()
use Doctrine\Common\Persistence\ObjectManager; // Utile à la fonction create() permet de faire persister et d'envoyer nos annonces dans la bdd
use Symfony\Component\HttpFoundation\Request; // Utile à la fonction create() pour récupérer le post de l'annonce (la requète)

class AdController extends AbstractController
{
    /**
     * Permet d'afficher toutes nos annonces
     * 
     * @Route("/ads", name="ads_index")
     */
    public function index(AdRepository $repo)
    {
        // Création d'un repository ($repo) dans le but d'aller chercher dans la bdd nos annonces (On peut supprimer cette étape à partir du moment ou on a fait l'injection de dépendances dans les paramètres de la fonction)
        //$repo = $this->getDoctrine()->getRepository(Ad::class); // la méthode getDoctrine (qui appartient à la classe AbstractController) permet de parler avect doctrine,
        //et getRepository est une méthode qui appartient à doctrine et permet de récupérer un repository (ici nons annonce, donc la classe Ad())

        $ads = $repo->findAll(); // On va chercher toutes les annonces de la bdd (méthode findAll()), $ads sera sous forme d'un tableau
        
        // ici 'ads' => $ads veut dire que l'on défini une variable (donc 'ads') utilisable dans twig qui aura comme valeur $ads (soit un tableau qui contient toutes les annonces)
        return $this->render('ad/showAds.html.twig', [
            'ads' => $ads
        ]);
    }

    /**
     * Permet de Gérer notre formulaire de création d'annonces
     * Paramètre Request $request est une injection de dépendance (permettra de récupérer le post, la requète du formulaire via sa méthode handleRequest())
     * Paramètre ObjectManager $manager est une injection de dépendance (permet de faire persister et envoyer le contenu du formulaire dans la bdd)
     *
     * @Route("/ads/newAd", name="ads_create")
     * 
     * @return Response
     */
    public function create(Request $request, ObjectManager $manager){
        $ad = new Ad(); 

        /* VERSION D'ESSAI DE CREATION DE SOUS FORMULAIRE 
        // CREATION DE 2x2 champs (url et legends) pour notre 'sous' formulaire de collection d'images
        $image = new Image(); // On ajoute notre classe Image
        $image->setUrl('http://placehold.it/400x200')
              ->setCaption("Titre d'essai de legende");

        $image2 = new Image(); // On ajoute notre classe Image
        $image2->setUrl('http://placehold.it/400x200')
            ->setCaption("Titre d'essai de legende n°2");
        $ad->addImage($image);
        $ad->addImage($image2);
        */

        // La fonction createForm() permet de créer un formulaire externe (soit ici la classe AnnonceType, et en second paramètre $ad qui est notre annonce afin de faire le lien entre les 2)
        $form = $this->createForm(AnnonceType::class, $ad);

        $form->handleRequest($request); // La fonction handleRequest(), avec en paramètre la requête, permet de parcourir la requête et d'extraire les informations du form
        //dump($ad); // Affiche le contenu taper dans le formulaire de l'annonce

        /*  TEST SUR LES FLASHES
        // addFlash() sert à notifier l'utilisateur de ce qui à été fait (param 1 = type de message, param 2 = message)
        $this->addFlash(
            'success',
            "L'annonce <strong>Test</strong> a bien été enregistrée !"
        );
        $this->addFlash(
            'warning',
            "L'annonce <strong>Test</strong> est erronnée !"
        );
        $this->addFlash(
            'success',
            "L'annonce <strong>Test</strong> est OK !"
        );
        $this->addFlash(
            'danger',
            "L'annonce <strong>Test</strong> n'a pas été enregistrée !"
        );
        */

        // SOUMISSION DU FORMULAIRE D'ANNONCE
        if($form->isSubmitted() && $form->isValid()){
            // Boucle pour faire persister les images de la collection d'images (c'est un tableau d'images avec url et caption, donc boucle foreach)
            foreach($ad->getImages() as $image){
                $image->setAd($ad); // On précise que chaque $image appartient à l'annonce ($ad)
                $manager->persist($image); // On fait persister chaque images
            }

            // Rappel: getUser() permet de récupérer l'utilisateur actuellement connecté (fonction globale symfony)
            $ad->setAuthor($this->getUser()); // On modifie l'auteur de l'annonce en lui indiquant que c'est l'utilisateur connecté ($this->getUser())

            //$manager = $this->getDoctrine()->getManager(); // On appel le manager de doctrine (c'est lui qui gère les modifications de la bdd) (Ligne inutile avec l'injection de dépendance)
            $manager->persist($ad); // On fait persister $ad (qui contient les données tapée dans le formulaire)
            $manager->flush(); // On envoie la requête SQL

            // addFlash() sert à notifier l'utilisateur de ce qui à été fait (param 1 = type de message, param 2 = message)
            $this->addFlash(
                'success',
                "L'annonce <strong>Test</strong> a bien été enregistrée !"
            );

            // Fonction redirectToRoute() qui créé un Response qui demande une redirection sur une autre page (ici on veut rediriger vers la visu de notre annonce fraichement créé)
            // Et en paramètre: la route voulue (cad l'annotation @Route), puis comme il faut un slug pour cette adresse, le slug voulu ($ad->getSlug())
            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug()
            ]); 
        }

        return $this->render('ad/newAd.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition
     *
     * @Route("/ads/{slug}/editAd", name="ads_edit")
     * 
     * @return Response
     */
    public function editAd( Ad $ad, Request $request, ObjectManager $manager){

        $form = $this->createForm(AnnonceType::class, $ad);

        $form->handleRequest($request);

        // SOUMISSION DU FORMULAIRE D'ANNONCE
        if($form->isSubmitted() && $form->isValid()){
            // Boucle pour faire persister les images de la collection d'images (c'est un tableau d'images avec url et caption, donc boucle foreach)
            foreach($ad->getImages() as $image){
                $image->setAd($ad); // On précise que chaque $image appartient à l'annonce ($ad)
                $manager->persist($image); // On fait persister chaques images
            }

            //$manager = $this->getDoctrine()->getManager(); // On appel le manager de doctrine (c'est lui qui gère les modifications de la bdd) (Ligne inutile avec l'injection de dépendance)
            $manager->persist($ad); // On fait persister $ad (qui contient les données tapée dans le formulaire)
            $manager->flush(); // On envoie la requête SQL

            // addFlash() sert à notifier l'utilisateur de ce qui à été fait (param 1 = type de message, param 2 = message)
            $this->addFlash(
                'success',
                "L'annonce <strong>Test</strong> a bien été modifiée !"
            );

            // Fonction redirectToRoute() qui créé un Response qui demande une redirection sur une autre page (ici on veut rediriger vers la visu de notre annonce fraichement créé)
            // Et en paramètre: la route voulue (cad l'annotation @Route), puis comme il faut un slug pour cette adresse, le slug voulu ($ad->getSlug())
            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug()
            ]); 
        }

        return $this->render('ad/editAd.html.twig', [
            'form' => $form->createView(),
            'ad' => $ad
        ]);

    }

    /**
     * Permet d'afficher une seule annonce
     * 
     * Le paramètre ci dessous {slug} permet de passer une variable dans la route (ici le slug soit le titre de l'annonce formater en url)
     * @Route("/ads/{slug}", name="ads_show")
     *
     * @return Response
     */
    public function showAd(Ad $ad){ // Le paramétre $ad stockera (grâce au @ParamCoverter de symfony) l'annonce de la classe Ad
        // On récupère l'annonce qui correspond au slug
        //$ad = $repo->findOneBySlug($slug); // findOneByX = trouve un élément / findByX trouve plusieurs éléments

        return $this->render('ad/showAd.html.twig', [
            'ad' => $ad
        ]);
    }

    
}

