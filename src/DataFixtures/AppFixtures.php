<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\DateTime;


class
AppFixtures extends Fixture
{
    /**
     * Stockage du service d'encodage des mot de passe de Symfony
     */
    private $encoder;

    private $slugger;

    /**
     * On utilise le constructeur pour demander à Symfony de récupérer le service d'encodage des mot de passe, pour ensuite le stocker dans $this->>encoder
     */
    public function __construct(UserPasswordHasherInterface $encoder, SluggerInterface $slugger){

        $this->encoder = $encoder;
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {

        // Instanciation du Faker en langue fr
        $faker = Faker\Factory::create('fr_FR');

        // Création du compte admin de Batman
        $admin = new User();

        $admin
            ->setEmail('admin@a.a')
            ->setRegistrationDate( $faker->dateTimeBetween('-1 year', 'now') )
            ->setPseudonym('Batman')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword(
                $this->encoder->hashPassword($admin, 'Hamza1995!')
            )
        ;

        $manager->persist($admin);

        // Stockage dans un array du compte de Batman (servira plus bas pour les commentaires)
        $listOfAllUsers[] = $admin;

        // Création de 10 comptes utilisateur (avec une boucle)
        for($i = 0; $i < 10; $i++){

            $user = new User();

            $user
                ->setEmail($faker->email )
                ->setRegistrationDate( $faker->dateTimeBetween('-1 year', 'now') )
                ->setPseudonym($faker->userName )
                ->setPassword(
                    $this->encoder->hashPassword($user, 'Hamza1995!')
                )
            ;

            $manager->persist($user);

            // Stockage dans l'array des utilisateurs créés (servira plus bas pour les commentaires)
            $listOfAllUsers[] = $user;

        }

        // Création de 200 articles (avec une boucle)
        for($i = 0; $i < 200; $i++){
            $article = new Article();

            $article
                ->setTitle( $faker->sentence(10) )
                ->setContent( $faker->paragraph(15) )
                ->setPublicationDate( $faker->dateTimeBetween('-1 year', 'now') )
                ->setAuthor( $admin) // Batman sera l'auteur de tous les articles
                ->setSlug( $this->slugger->slug($article->getTitle())->lower())


            ;

            $manager->persist($article);

            // Création entre 0 et 10 commentaire aléatoire par article (avec une boucle=
            $rand = rand(0,10);

            for($j = 0; $j < $rand; $j++){

                $comment = new comment();

                $comment
                    ->setContent( $faker->paragraph(5) )
                    ->setPublicationDate( $faker->dateTimeBetween('-1 year', 'now') )
                    ->setAuthor( $faker->randomElement($listOfAllUsers) )
                    ->setArticle( $article )


                ;

                $manager->persist($comment);

            }


        }

        $manager->flush();
    }
}
