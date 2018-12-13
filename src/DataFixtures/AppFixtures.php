<?php

namespace App\DataFixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

//use Cocur\Slugify\Slugify; // A ajouter pour se servir de la classe Slugify
use App\Entity\Image; // A ajouter pour se servir de la classe Image
use App\Entity\Ad; // A ajouter pour se servir de la classe Ad
use Faker\Factory; // A ajouter pour se servir de la classe Factory de Faker

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        $faker = Factory::create('fr-FR'); // Utilisation de la librairie Faker via la classe Factory aver la localisation francaise ('fr-FR')
        //$slugify = new Slugify(); // Utilisation de la librairie Slugify (formate au format URL)

        // Boucle de création de 30 annonces (dans laquel une seconde boucle va ajouter entre 2 et 5 images)
        for($i = 1; $i <= 30; $i++){
            $ad = new Ad();

            $title = $faker->sentence(); // Va générer une phrase (grâce à la librairie Faker)
            //$slug = $slugify->slugify($title); // Ici on transforme notre titre ($title) en une URL (cad sans caractère spécial, sans espace...) et on le stock dans la variable $slug
            $coverImage = $faker->imageUrl(1000,350); // Va générer une image de 1000px x 350px
            $introduction = $faker->paragraph(2); // Va générer 1 paragraphe en lorem ipsum de 2 phrases
            // Par défaut la méthode paragraphs() renvoie un tableau, c'est pour cela qu'il faut formater les paragraphes (join est une méthode php qui sépare les éléments d'un tableau)
            $content = '<p>' . join('</p><p>', $faker->paragraphs(5)).'</p>'; // Va générer 5 paragraphes formatés en html par les balise p

            // Ici on rempli les "champs" de la table ad (annonce) de notre bdd. En fait on rempli les seteur des la classe Ad
            $ad->setTitle($title)
                //->setSlug($slug) // $slug est un identifiant textuel pouvant servir d'URL (nous l'avons formaté plus haut via $title)
                ->setCoverImage($coverImage)
                ->setIntroduction($introduction)
                ->setContent($content)
                ->setPrice(mt_rand(40, 200))
                ->setRooms(mt_rand(1, 5));

                // Seconde boucle qui va permettre d'ajouter dans chaque annonce entre 2 et 5 images
                for($j = 1; $j <= mt_rand(2, 5); $j++){
                    $image = new Image();

                    // On rempli les setteur de Image
                    $image->setUrl($faker->imageUrl()) // Faker va générer une url d'image au hasard (imageUrl())
                          ->setCaption($faker->sentence()) // Faker va généere une phrase au hasard pour la légende de l'image
                          ->setAd($ad); // ici on met notre annonce (celle qui est liée à l'image)

                    $manager->persist($image);
                }

            $manager->persist($ad);
        }

        $manager->flush();
    }
}
