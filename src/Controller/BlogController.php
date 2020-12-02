<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TexteraType;
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
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager)
    {
        // Nous avons définit 2 routes différentes, une pour l'insertion et une pour la modification
        // Lorsque l'on envoie la route '/blog/new' dans l'URL, on définit un Article $article NULL, sinon Symfony tente de récupérer un article en BDD et nous avons une erreur
        // Lorsque l'on envoie la route '/blog/{id}/edit', Symfony selectionne en BDD l'article en fonction de l'ID transmit dans l'URL et écrase NULL par l'article recupéré en BDD dans l'objet $article


        // La classe Request contient toute les données véhiculées par les supergloables ($_POST, $_GET, $_FILES etc...)
        // dump($request); // on observe les données saisi dans le formulaire dans la propriété 'request'

        // Méthode 1 - Mis en commentaire - Lié au formulaire de create.html.twig dans templates/blog
        // Si des données ont bien été saisie dans le formulaire
        // if($request->request->count() > 0)
        // {
        //     // Pour pouvoir insérer un article dans la BDD, nous devons passer par l'entité Article et remplir tout les setteurs de l'objet
        //     $article = new Article;
        //     $article->setTitle($request->request->get('title'))
        //             ->setContent($request->request->get('content'))
        //             ->setImage($request->request->get('image'))
        //             ->setCreatedAt(new \DateTime());

        //     $manager->persist($article); // On prépare la requete d'insertion
        //     $manager->flush(); // on execute la requete d'insertion

        //     // redirectToRoute permet de rediriger vers le detail de l'article après insertion
        //     // 2 arguments : La route et le paramètre attendu dans la route (ID)
        //     return $this->redirectToRoute('blog_show', [
        //         'id' => $article->getId()
        //     ]);
        // }
        // Fin méthode 1


        // On entre dans la condition IF seulement dans le cas de la création d'un nouvel article, c'est à dire pour la route 'blog/new', $article est NULL, on crée un nouvel objet Article
        // Dans le cas d'une modification, $article n'est pas null, il contient l'article selctionné en BDD à modifier, on entre pas dans la condition IF
        if (!$article) {
            $article = new Article;
        }

        // On observe quand remplissant l'objet Article via les setters, les getters renvoient les données de l'article directement dans les champs du formulaire
        //$article->setTitle("Titre à la c")
        //        ->setContent("contenu à la c");

        // createFormBuilder() : méthode issue de la classe BlogController permettant de créer un formulaire HTML qui sera lié à notre objet Article, c'est à dire que les champs du formulaire vont remplir l'objet Article
        /* Remplacé par DataFixtures/Form
        $form = $this->createFormBuilder($article)
            ->add('title')

            ->add('content')

            ->add('image')

            ->getForm(); // permet de valider le formulaire
        */

        // On importe la classe ArticleType qui permet de générer le formulaire d'ajout / modification des articles
        // On précise que le formulaire a pour but de remplir les setteurs de l'objet $article
        $form = $this->createForm(ArticleType::class, $article);
        
        $form->handleRequest($request);   // handleRequest permet de vérifier si tout les champs ont bien été remplit et la méthode va bindé l'objet Article, c'est à dire que si un titre de l'article a été saisi, il sera envoyé directement dans le bon setter de l'objet Article

        dump($request); // on observe les données saisi dans le formulaire dans la propriété 'request'

        // Si le formulaire a bien été soumit et que toutes les données sont valides, alors on entre dans la condition IF
        if($form->isSubmitted() && $form->isValid())  // 
        {
            if(!$article->getId())
            {
                $article->setCreatedAt(new \DateTime());  // on remplit le setter de la date pisque nous n'avons pas de champ date dans le formulaire
            }
            $manager->persist($article);  // on prépare l'insertion
            $manager->flush();  // On execute l'insertion en BDD

            // Une fois l'insertion éxecutée, on redirige vers le détail de l'article qui vient d'être inséré
            return $this->redirectToRoute('blog_show', [
                        'id' => $article->getId()   // On transmet dans la route l'ID de l'article qui vient d'être inséré grace au getter de l'objet Article
            ]);
        }

        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null   //Si l'article est différent de NULL, alors 'editMode' renvoie TRUE et que c'est une modification
        ]);
    }

    
    // Nous utilisons le concept de route paramétrées pour faire en sorte de récupérer le bon ID du bon article
    // Nous avons définit le paramètre de type {id} directement dans la route

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article, Request $request, EntityManagerInterface $manager): Response // 1
    {

        // ON appel le repository de la classe Article afin de selectionner dans la table Article 
        // $repo = $this->getDoctrine()->getRepository(Article::class);

        // La méthode find() issue de la classe ArticleRepository permet de selectionner un article en BDD en fonction de son ID
        // $article = $repo->find($id); // 1

        // dump($article);

        $comment = new Comment;

        // dump($request);
        
        // $user = $this->getUser()->getUsername();
        // dump($user);

        $formComment = $this->createForm(CommentType::class, $comment);   // importation du formulaire d'ajout de commentaire relié à l'entité $comment
        
        $formComment->handleRequest($request);   // on rempli l'objet (entité) $comment avec les données saisies dans le formulaire
        
        // Si le formulaire a bien été validé, on entre dans la condition IF
        if($formComment->isSubmitted() && $formComment->isValid())
        {
            // getUser() : permettant de récupérer les données de l'utilisateur en session
            // On stock le nom d'utilisateur dans la variable ¤username
            $username = $this->getUser()->getUsername();
            dump($username);

            // On renseigne le setter de l'auteur afin qu'il soit automatiquement compris dans le commentaire
            $comment->setAuthor($username);
            $comment->setCreatedAt(new \DateTime);   // on insère une date de création du commentaire
            $comment->setArticle($article);   // On relie le commentaire à l'article (clé étrangère)


            $manager->persist($comment);   // on prépare l'insertion
            $manager->flush();   // on execute l'insertion

            // Envoi d'un message de validation
            $this->addFlash('success', "Le commentaire a bien été posté !");

            // On redirige vers l'article après l'insertion du commentaire
            return $this->redirectToRoute('blog_show',[
                'id' => $article->getId()
            ]);
        }



        return $this->render('blog/show.html.twig', [
            'article' => $article,    // on envoie sur le template l'article selectionné en BDD
            'formComment' => $formComment->createView(),
            
        ]);
    }

    /*
        Symfony comprend qu'il y a un article a passé et que dans la route il y a un ID, il va donc chercher le bon article avec le bon identifiant.
        Tout ça grace au ParamConverter de Sumfony, en gros ilm voit que l'on a besoin d'un article et aussi d'un ID, il va donc chercher l'article avec l'identifiant et l'nevoyer à la fonction show()
        Nous avons donc des fonctions beaucoup plus courte !!
    */


}
