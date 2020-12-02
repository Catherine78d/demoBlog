<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        //$user = $this->getUser();

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/articles", name="admin_articles")
     */
    public function adminArticles(EntityManagerInterface $manager, ArticleRepository $repo): Response
    {
        // via le manager qui permet de manipuler la BDD (insert, update, delete ...), on execute la méthode getClassMetadata() 
        // afin de sélectionner les méta données (primary ) (primary key, not null, noms des champs ...) d'une entité (donc d'une table SQL), pour sélectionner le nom des champs/colonnes de la table grace à la méthode getFieldNames()
        $colonnes = $manager->getclassMetadata(Article::class)->getFieldNames();

        dump($colonnes);

        // On selectionne l'ensempble des articles de la table SQL 'article' dans la BDD en passant par la classe ArticleRepository qui permet de sélectionner dans la table SQL 'article' 
        // et la méthode 'findAll()' qui permet de sélectionner l'ensemble de la table (SELECT * FROM article + FETCHALL)
        $articles = $repo->findAll();

        dump($articles);

        return $this->render('admin/admin_articles.html.twig', [
            'colonnes' => $colonnes,
            'articles' => $articles
            ]);

        }

    /**
     * @Route("/admin/article/new", name="admin_new_article")
     * @Route("/admin/{id}/edit-article", name="admin_edit_article")
     */
    public function adminForm(Request $request, EntityManagerInterface $manager, Article $article = null): Response
    {
        /*
            1. Importer le formulaire de création des articles (form/aricleType)
            2. Transmettre le formulaire à la méthode render et l'afficher dans le template admin_create.html.twig 
            3. Faites de récupérer les données du formulaire et les afficher dans un dump()
            4. Réaliser le traitement PHP permettant d'insérer un nouvel article à la validation du formulaire
            5. Executer une redirection après insertion vers l'affichage des articles dans le backOffice
            6. Afficher un message de validation
        */

        if(!$article) 
        {
            $article = new Article;
        }

        $formArticle = $this->createForm(ArticleType::class, $article);

        dump($request);

        $formArticle->handleRequest($request);

        if($formArticle->isSubmitted() && $formArticle->isValid())
        {
            if (!$article->getId()) 
            {
                $article->setCreatedAt(new \DateTime);
                $this->addFlash('success',"L'article a bien été enregistré");
            }
            else 
            {
                $this->addFlash('success',"L'article a bien été modifié");
            }

            $manager-> persist($article);
            $manager->flush();

            $this->addFlash('success',"L'article a bien été enregistré");

            return $this->redirectToRoute('admin_articles');
        }

        return $this->render('admin/admin_create.html.twig', [
            'formArticle' => $formArticle->CreateView()
        ]);
    }

}
