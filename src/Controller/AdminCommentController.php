<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\AdminCommentType;
use App\Service\PaginationService;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCommentController extends AbstractController
{
    /**
     * Affiche la page d'administration de tout les commentaires
     * le "requirements={"page": "\d+"}" permet de contraindre le paramètre "{page}" dans le routing en lui donnant une Regex qui dis ici que "page" doit être un nombre ou des nombres.
     * On initialise à 1 la variable $page (utilisée dans le routing) qui va nous servir lors de la pagination des annonces. "page" est par défaut = 1 soit notre page de départ
     * 
     * @Route("/admin/comments/{page}", name="admin_comments_index", requirements={"page": "\d+"})
     */
    public function index(CommentRepository $repo, $page = 1, PaginationService $pagination) // On oublie pas "d'injecter" le repository du commentaire (pour pouvoir communique avec la bdd)
    {
        // CONFIGURATION DE LA PAGINATION (utilisation de la classe PaginationService)
        $pagination->setEntityClass(Comment::class) // $pagination reçoit la classe Ad.php
                   ->setCurrentPage($page) // $pagination reçoit la page actuelle (dans le routing, et initialisé dans les param de la fonction index)
                   ->setLimit(10); // On définit la limite du nb d'annonce à afficher par page

        /*
        dump($pagination->getData()); // Affiche les commentaires (dans la limitation données par la méthode getData(), soit ici une limite de 10)
        die();
        */

        return $this->render('admin/comments/admin_comments.html.twig', [
            'pagination' => $pagination 
        ]);
    }

    /**
     * Permet de modifier un commentaire en mode admin
     * 
     * @Route("/admin/comment/{id}/edit", name="admin_comments_edit")
     *
     * @param Comment $comment
     * @return Response
     */
    public function edit(Comment $comment, Request $request, ObjectManager $manager){

        // On stock le contenu de commentaire avant soumission du formulaire (dans le but de le comparer après la validation du formulaire)
        $ancienComment = $comment->getContent(); 
        // Création du formulaire de type AdminCommentType.php qu'on relie avec notre "$comment" récupérée en paramètre
        $form = $this->createForm(AdminCommentType::class, $comment); 

        $form->handleRequest($request);

        // On valide et soumet le formulaire
        if($form->isSubmitted() && $form->isValid()){
            // On récupère le commentaire fraichement sousmis dans le formulaire (dans le but de le comparer ci-après)
            $newComment = $comment->getContent(); 
            // Si le commentaire à été modifié (s'il est différent) alors... (Sinon message que la modif n'a pas été effectuée)
            if($newComment !== $ancienComment){
                $manager->persist($comment); // On fait persister l'annonce
                $manager->flush(); // On envoie la requête

                // On prévient l'utilisateur avec un message Flash de la modif du commentaire
                $this->addFlash(
                    'success',
                    "Le commentaire <strong>{$comment->getId()}</strong> à bien été modifié !"
                );
                return $this->redirectToRoute("admin_comments_index"); // On redirige vers la page d'administration des commentaires
            }else{
                $this->addFlash(
                    'warning',
                    "Le commentaire <strong>n ° {$comment->getId()}</strong> n'a pas été modifié !"
                );
            }
        }
        
        return $this->render('admin/comments/edit_comment.html.twig', [
            'comment' => $comment,
            'newComment' => $newComment,
            'ancienComment' => $ancienComment,
            'form' => $form->createView() // On stock dans 'form' la vue du formulaire
        ]);
    }

    /**
     * Permet de supprimer un commentaire en mode administrateur
     * 
     * @Route("/admin/comments/{id}/delete", name="admin_comment_delete")
     *
     * @param Comment $comment
     * @param ObjectManager $manager
     * @return Response
     */
    public function delete(Comment $comment, ObjectManager $manager){
        
        $manager->remove($comment); // On supprime le commentaire
        $manager->flush(); // On envoie à la bdd

        $this->addFlash(
            'success',
            "Le commentaire <strong>{$comment->getId()}</strong> à bien été supprimée !"
        );
        
        return $this->redirectToRoute('admin_comments_index'); // On redirige vers la page de modération des commentaires
    }
}
