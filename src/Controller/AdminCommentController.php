<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\AdminCommentType;

class AdminCommentController extends AbstractController
{
    /**
     * Affiche la page d'administration de tout les commentaires
     * 
     * @Route("/admin/comments", name="admin_comments_index")
     */
    public function index(CommentRepository $repo) // On oublie pas "d'injecter" le repository du commentaire (pour pouvoir communique avec la bdd)
    {
        return $this->render('admin/comments/admin_comments.html.twig', [
            'comments' => $repo->findAll() // On va chercher nos commentaires (dans la bdd) grace au repository auquel on applique la méthode findAll() de symfony
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
