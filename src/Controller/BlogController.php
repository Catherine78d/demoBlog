<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    // Chaque méthode du controller est associé à une route bien spécifique
    // Lorsque nous envoyons la route '/blog' dans l'URL du navigateur, cela execute automatiquement dans le controller, la méthode associé à celle-ci
    // Chaque méthoder renvoi un template sur le navigateur en fonction de la route transmise

    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo): Response
    {
        // On appel la classe Repository de la classe Article
        // Une classe Repository permet uniquement de selectionner des données en BDD
        // $repo = $this->getDoctrine()->getRepository(Article::class);
        // dump($repo);

        // findAll() est une méthode issue de la classe ArticleRepository et permet de selectionner l'ensemble d'une table SQL (SELECT * FROM)
        $articles = $repo->findAll();
        dump($articles);

        return $this->render('blog/index.html.twig', [
            'articles' => $articles // Nous envoyons sur le template les articles selectionnés en BDD
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        return $this->render('blog/home.html.twig', [
            'title' => 'Bienvenue sur le blog Symfony',
            'age' => 25
        ]);
    }


    /**
     * @Route("/blog/new", name="blog_create")
     */
    public function create(Request $request, EntityManagerInterface $manager)
    {
        // La classe Request contient toute les données véhiculées par les supergloables ($_POST, $_GET, $_FILES etc...)
        dump($request); // on observe les données saisi dans le formulaire dans la propriété 'request'

        // Si des données ont bien été saisie dans le formulaire
        if($request->request->count() > 0)
        {
            // Pour pouvoir insérer un article dans la BDD, nous devons passer par l'entité Article et remplir tout les setteurs de l'objet
            $article = new Article;
            $article->setTitle($request->request->get('title'))
                    ->setContent($request->request->get('content'))
                    ->setImage($request->request->get('image'))
                    ->setCreatedAt(new \DateTime());

            $manager->persist($article); // On prépare la requete d'insertion
            $manager->flush(); // on execute la requete d'insertion

            // redirectToRoute permet de rediriger vers le detail de l'article après insertion
            // 2 arguments : La route et le paramètre attendu dans la route (ID)
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('blog/create.html.twig');
    }

    
    /*
        Nous utilisons le concept de route paramétrées pour faire en sorte de récupérer le bon ID du bon article
        Nous avons définit le paramètre de type {id} directement dans la route
    */

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article): Response // 1
    {
        // ON appel le repository de la classe Article afin de selectionner dans la table Article 
        // $repo = $this->getDoctrine()->getRepository(Article::class);

        // La méthode find() issue de la classe ArticleRepository permet de selectionner un article en BDD en fonction de son ID
        // $article = $repo->find($id); // 1
        dump($article);

        return $this->render('blog/show.html.twig', [
            'article' => $article // on envoie sur le template l'article selectionné en BDD
        ]);
    }

    /*
        Symfony comprend qu'il y a un article a passé et que dans la route il y a un ID, il va donc chercher le bon article avec le bon identifiant.
        Tout ça grace au ParamConverter de Sumfony, en gros ilm voit que l'on a besoin d'un article et aussi d'un ID, il va donc chercher l'article avec l'identifiant et l'nevoyer à la fonction show()
        Nous avons donc des fonctions beaucoup plus courte !!
    */

   
}
