<?php

namespace App\DataFixtures;


use App\Entity\Role;
use App\Entity\User;
use App\Entity\Ad; 
use App\Entity\Image; 
use App\Entity\Booking;
use App\Entity\Comment; 

//use Cocur\Slugify\Slugify; // A ajouter pour se servir de la classe Slugify
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory; 
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AppFixtures extends Fixture
{
    //
    private $encoder;
    
    // Fonction de construction qui a pour but de pouvoir se servir de l'encodage de symfony (ne pas oublier de configurer le fichier dans packages/security.yaml en précisant le type d'encodage et sur quelle entité l'appliquer)
    public function __construct(UserPasswordEncoderInterface $encoder){
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // Utilisation de la librairie Faker via la classe Factory aver la localisation francaise ('fr_FR')
        $faker = Factory::create('fr_FR'); 
        //$slugify = new Slugify(); // Utilisation de la librairie Slugify (formate au format URL)


        // Création d'un Role administrateur
        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        // Création d'un utilisateur qui aura comme Role celui d'administrateur
        $adminUser = new User();
        $adminUser->setFirstName('Jérémie')
                  ->setLastName('Genet')
                  ->setEmail('jamyjam82377@gmail.com')
                  ->setHash($this->encoder->encodePassword($adminUser, '16641664'))
                  ->setPicture('http://randomuser.me/api/portraits/men/1.jpg')
                  ->setIntroduction($faker->sentence())
                  ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                  ->addUserRole($adminRole); // On donne à notre utilisateur le role créé au dessus (celui d'administrateur)
        $manager->persist($adminUser);

        // GESTION DES FAKES UTILISATEURS
        $users = []; // Création d'un tableau qui contiendra après son passage dans la boucle les utilisateurs (soit des tableaux)
        $genres = ['male', 'female']; // Création d'un tableau qui contient 2 genres (dans le but de piocher au hasard l'un des deux)

        for($i = 1;$i <= 10; $i++){
            $user = new User();

            $genre = $faker->randomElement($genres); // La méthode randomElement de faker retourne au hasard le contenu d'un tableau (ici soit "male" soit "female")

            // Utilisation de l'API RANDOMUSER dont les images sont à une url de type : http://randomuser.me/api/portraits/women/1.jpg
            // le but va être de définir des variable pour le genre et le numéro d'image de l'url, afin de randomiser l'image
            $picture = 'http://randomuser.me/api/portraits/';
            $pictureId = $faker->numberBetween(1, 99) . '.jpg'; // la méthode de faker numberBetween() permet de rendre un nombre ici entre 1 et 99

            // Condition en fonction du genre et du numéro d'image (qui sont random)
            if($genre == "male"){
                $picture = $picture . 'men/' . $pictureId;
            }else{
                $picture = $picture . 'women/' . $pictureId;
            }

            // On va stocker dans $hash le résultat de l'encodage (encodeur est une instance de UserPasswordEncoderInterface() voir constructeur)
            // La méthode encodePassword() encode un mot de passe avec l'algorithme choisi (param1 = l'entité sur laquelle on veut opérer, param2 = le password que l'on veut encoder)
            $hash = $this->encoder->encodePassword($user, 'password'); 

            $user->setFirstName($faker->firstname($genre)) // On passe en argument $genre à la méthode de faker (si c'est "male" faker définira un faux firstname masculin, sinon féminin)
                 ->setLastName($faker->lastname)
                 ->setEmail($faker->email)
                 ->setIntroduction($faker->sentence())
                 ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                 ->setHash($hash) // $hash représente donc ici le password utilisateur mais il est encodé!!
                 ->setPicture($picture); // $picture représente donc l'url d'image d'avatar random de l'API RANDOMUSER

            $manager->persist($user);
            $users[] = $user; // A la fin de la boucle de 10 (si c'est une boucle de 10) nous aurons 10 utilisateurs (10 tableaux de données) dans le tableau $users[]

        }
        

        // GESTION DES FAKES ANNONCES
        // Boucle de création de 30 annonces (dans laquel une seconde boucle va ajouter entre 2 et 5 images)
        for($i = 1; $i <= 30; $i++){
            $ad = new Ad();

            $title = $faker->sentence(); // Va générer une phrase (grâce à la librairie Faker)
            //$slug = $slugify->slugify($title); // Ici on transforme notre titre ($title) en une URL (cad sans caractère spécial, sans espace...) et on le stock dans la variable $slug
            $coverImage = $faker->imageUrl(1000,350); // Va générer une image de 1000px x 350px
            $introduction = $faker->paragraph(2); // Va générer 1 paragraphe en lorem ipsum de 2 phrases
            // Par défaut la méthode paragraphs() renvoie un tableau, c'est pour cela qu'il faut formater les paragraphes (join est une méthode php qui sépare les éléments d'un tableau)
            $content = '<p>' . join('</p><p>', $faker->paragraphs(5)).'</p>'; // Va générer 5 paragraphes formatés en html par les balise p

            // Pour chaque annonce, on sélection un utilisateur au hasard (entre 0 et le nombre d'utilisateur disponible, le "-1" parce que c'est un tableau)
            $user = $users[mt_rand(0, count($users) -1)];

            // Ici on rempli les "champs" de la table ad (annonce) de notre bdd. En fait on rempli les seteur des la classe Ad
            $ad->setTitle($title)
                ->setCoverImage($coverImage)
                ->setIntroduction($introduction)
                ->setContent($content)
                ->setPrice(mt_rand(40, 200))
                ->setRooms(mt_rand(1, 5))
                ->setAuthor($user); // author sera le user déterminé un peu plus haut de façon random

                // GESTION DES FAKES IMAGES de la collection
                // Seconde boucle qui va permettre d'ajouter dans chaque annonce entre 2 et 5 images
                for($j = 1; $j <= mt_rand(2, 5); $j++){
                    $image = new Image();

                    // On rempli les setteur de Image
                    $image->setUrl($faker->imageUrl()) // Faker va générer une url d'image au hasard (imageUrl())
                          ->setCaption($faker->sentence()) // Faker va généere une phrase au hasard pour la légende de l'image
                          ->setAd($ad); // ici on met notre annonce (celle qui est liée à l'image)

                    $manager->persist($image);
                }

            // GESTION DES RESERVATIONS
            for($j = 1; $j <= mt_rand(0, 10); $j++){
                $booking = new Booking();

                // $createAt et $startDate sont des objet de type DateTime de php
                $createdAt = $faker->dateTimeBetween('-6 months'); // le "('-6 months')" signifie entre il y a 6 mois et maintenant
                $startDate = $faker->dateTimeBetween('-3 months'); // $startDate est la date de début de la réservation
                // GESTION DE LA DATE DE FIN DE LA RESERVATION
                $duration = mt_rand(2, 8); // $duration représente la durée du séjour (entre 2 et 8)
                // $endDate correspond à la date de fin de la réservation soit : la date de debut ($startDate) + la duréé ($duration) = la date de fin ($endDate)
                // ASTUCE : 'clone" permet ici de cloner la date de départ mais ne pas altérer la date de départ que l'on a configuré plus haut (dans le but que $endDate et $startDate ne soit pas les mêmes)
                $endDate = (clone $startDate)->modify("+$duration days"); // la méthode modify() de php permet d'ajouter ou enlever des jours à une date
                $amount = $ad->getPrice() * $duration; // Le montant de la réservation vaudra le prix de l'annonce x le nombre de jours du séjour ($duration)
                $booker = $users[mt_rand(0, count($users) -1)]; // le réservateur sera un utilisateur au hasard (entre 0 et le nombre d'utilisateur disponible, le "-1" parce que c'est un tableau)
                $comment = $faker->paragraph();

                // On rempli les 'champs' de la table booking (réservation) de notre bdd
                $booking->setBooker($booker)
                        ->setAd($ad)
                        ->setStartDate($startDate)
                        ->setEndDate($endDate)
                        ->setCreatedAt($createdAt)
                        ->setAmount($amount)
                        ->setComment($comment); // champ de commentaire du formulaire de réservation

                $manager->persist($booking);

                // GESTION DES COMMENTAIRES DES ANNONCES
                // On tire à pile ou face (0 ou 1) pour savoir si une annonce aura un commentaire ou pas
                if(mt_rand(0, 1)){
                    $comment = new Comment();
                    $comment->setContent($faker->paragraph())
                            ->setRating(mt_rand(1, 5)) // On génère une note random de 1 a 5
                            ->setAuthor($booker) // l'auteur du commentaire sera forcément celui qui est l'auteur de la réservation donc $booker défini plus haut
                            ->setAd($ad); // On met l'annonce dont parle la réservation

                    $manager->persist($comment);
                }

            }

            $manager->persist($ad);
        }

        $manager->flush();
    }
}
