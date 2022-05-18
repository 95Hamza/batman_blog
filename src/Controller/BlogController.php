<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\NewArticleFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/blog', name: 'blog_')]
class BlogController extends AbstractController
{

    /**
     * Controlleur de la page permettant de créer un nouvel article
     *
     * Accés réservé aux administrateurs (ROLE_ADMIN)
     */
    #[Route('/nouvelle-publication/', name : 'new_publication')]
    #[IsGranted('ROLE_ADMIN')]
    public function  newPublication(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {

        $article = new Article();

        $form = $this-> createForm(NewArticleFormType::class, $article);

        $form-> handleRequest($request);

        // Si le formulaire est envoyé et sans erreur
        if ($form->isSubmitted() && $form->isValid()){

            // On termine d'hydrater l'article
            $article
                ->setPublicationDate(new \DateTime())
                ->setAuthor( $this->getUser() )
                ->setSlug( $slugger->slug( $article->getTitle() )->lower() )
            ;

            // Sauvegarde de l'article en BDD via le manager général des entités de Doctrine
            $em = $doctrine->getManager();
            $em->persist($article);
            $em->flush();

            // Message flash du succès
            $this->addFlash( 'success', 'Article publié avec succès !');

            // Redirection vers la page qui affiche l'article (en envoyant son id et son slug dans l'url)
            return $this->redirectToRoute('blog_publication_view', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
            ]);

        }

        return $this->render('blog/new_publication.html.twig', [
            'form' => $form->createView(),

        ]);
    }

    /**
     * Controleur de la page permettant du voir un article en détail (via id et slug dans l'url)
     */
    #[Route('/publication/{id}/{slug}/', name: 'publication_view')]
    #[ParamConverter('article', options: [ 'mapping' => [ 'id' => 'id', 'slug' => 'slug'] ])]
    public function publicationView(Article $article): Response
    {

        return $this->render('blog/publication_view.html.twig', [
            'article' => $article,
        ]);
    }


    /**
     *  Controleur de la page qui liste les articles
     */
    #[Route('/publication/liste/', name: 'publication_list')]
    public function publicationList(ManagerRegistry $doctrine): Response
    {

        $articleRepo = $doctrine->getRepository(Article::class);

        $articles = $articleRepo->findAll();

        return $this->render('blog/publication_list.html.twig', [
            'articles' => $articles,
        ]);
    }



}
