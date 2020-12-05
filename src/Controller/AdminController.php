<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Form\AdminCommentType;
use App\Repository\UserRepository;
use App\Form\AdminRegistrationType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
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
    public function adminFormArticle(Request $request, EntityManagerInterface $manager, Article $article = null): Response
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
            'formArticle' => $formArticle->CreateView(),
            'editMode' => $article->getId()
        ]);
    }

    /**
     * @Route("admin/{id}/delete-article", name="admin_delete_article")
     */
    public function deleteArticle(Article $article, EntityManagerInterface $manager)
    {
        // Nous avons définit une route paramétrée (id) afin de pouvoir supprimer cet article dans la BDD
        // Nous avons injecté en dépendance l'entité article afin que Symfony selectionne auutomatiquement en BDD l'article à supprimer
        // remove() : méthode de l'interface EntityManagerInterface qui permet de préparer et garder en mémoire la requête DELETE de suppression
        $manager->remove($article);
        $manager->flush();

        // On affiche un message de validation de suppression
        $this->addFlash('success', "L'article a bien été supprimé");

        // On redirige vers l'affichage des articles dans le backoffice après la suppression
        return $this->redirectToRoute('admin_articles');
    }

    /**
     * @Route("/admin/category", name="admin_category")
     */
    public function  adminCategory(EntityManagerInterface $manager, CategoryRepository $repo): Response
    {
        $colonnes = $manager->getclassMetadata(Category::class)->getFieldNames();

        dump($colonnes);

        $categories = $repo->findAll();  // SELECT * FROM category + FETCH_ALL (retour des array multidimentionnel)

        dump($categories);

        return $this->render('admin/admin_category.html.twig', [
            'colonnes' => $colonnes,
            'categories' => $categories
            ]);
    }

    /**
     * @Route("/admin/category/new", name="admin_new_category")
     * @Route("/admin/{id}/edit-category", name="admin_edit_category")
     */
    public function adminFormCategory(Request $request, EntityManagerInterface $manager, Category $category = null): Response
    {
        // L'entité Category représente un model de la table SQL Category, donc pour pouvoir insérer dans la table Category, nous devons renseigner les setter de l'objet avec les données du formulaire
        if (!$category) 
        {
            $category = new Category;
        }

        $formCategory = $this->createForm(CategoryType::class, $category);   // On crée le formulaire d'ajout/modif des catégories eet on relit le formulaire à l'entité $category

        dump($request);

        $formCategory->handleRequest($request); // on récupère les données du formulaire afin de les envoyer dans les setteurs de l'entité $category

        if($formCategory->isSubmitted() && $formCategory->isValid())
        {
            if(!$category->getId())
                $message = "La catégorie a bien été enregistrée";
            else
                $message = "La catégorie a bien été modifiée";

            // Message de validation
            $this->addFlash('success', $message);

            $manager->persist($category);   // on prépare et on garde en mémoire l'insertion
            $manager->flush();   // on execute l'insertion      

            // On redirige vers l'affichage des catégories dans le backoffice après l'enregistrement'
            return $this->redirectToRoute('admin_category');

        }

        return $this->render('admin/admin_create_category.html.twig', [
            'formCategory' => $formCategory->createView(),
            'editMode' => $category->getId()
        ]);
    }



    /**
     * @Route("admin/{id}/delete-category", name="admin_delete_category")
     */
    public function adminDeleteCategoty(Category $category, EntityManagerInterface $manager)
    {
        // Si le getter getArticles() est vide, cela veut dire qu'il n'y a plus d'articles associés à la catégorie, nous pouvons donc la supprimer
        if($category->getArticles()->isEmpty())
        {
            $manager->remove($category);
            $manager->flush();

            // On affiche un message de validation de suppression
            $this->addFlash('success', "La categorie a bien été supprimée");
        }
        else  // Sinon dans tous les autres cas, cela veut dire que des articles sont encore associés à la catégorie, nous ne pouvons donc pas la supprimer
        {
            // On affiche un message de validation de suppression
            $this->addFlash('danger', "Il n'est pas possible de supprimer la catégorie car des articles y sont toujours associés ");
        }

        // On redirige vers l'affichage des categories dans le backoffice après la suppression
        return $this->redirectToRoute('admin_category');
    }



    /**
     * @Route("/admin/comments", name="admin_comments")
     */
    public function adminComments(EntityManagerInterface $manager, CommentRepository $repo): Response
    {
        $colonnes = $manager->getclassMetadata(Comment::class)->getFieldNames();

        dump($colonnes);

        $comments = $repo->findAll();  // SELECT * FROM comment + FETCH_ALL (retour des array multidimentionnel)

        dump($comments);

        return $this->render('admin/admin_comments.html.twig', [
            'colonnes' => $colonnes,
            'comments' => $comments
            ]); 
    }

    /**
     * @Route("/admin/{id}/edit-comment", name="admin_edit_comment")
     */
    public function editComment(Comment $comment, Request $request, EntityManagerInterface $manager): Response
    {
        dump($comment);
        
        $formComment = $this->createForm(AdminCommentType::class, $comment);
        
        dump($request);
        $formComment->handleRequest($request);
        
        if($formComment->isSubmitted() && $formComment->isValid())
        {
            $manager->persist($comment);
            // $insert = $bdd->prepare("INSERT INTO comment (author) VALUES (:author)");
            // $insert->bindValue(':author')

            $manager->flush();
            // $insert->execute();

            $this->addFlash('success', "Le commentaire a bien été modifié");

            return $this->redirectToRoute('admin_comments');

        }

        return $this->render('admin/edit_comment.html.twig', [
            'formComment' => $formComment->createView()
        ]);
    }


    /**
     * @Route("admin/{id}/delete-comment", name="admin_delete_comment")
     */
    public function adminDeleteComment(Comment $comment, EntityManagerInterface $manager)
    {
        $manager->remove($comment);   // Préparation de la requête DELETE
        $manager->flush();   // execution de la requete DELETE

        // Message de validation de suppression
        $this->addFlash('success', "Le commentaire a bien été supprimée");

        // redirection vers l'affichage des commentaires dans le BACKOFFICE
        return $this->redirectToRoute('admin_comments');
    }





    /**
     * @Route("/admin/users", name="admin_users")
     */
    public function adminUserss(EntityManagerInterface $manager, UserRepository $repo): Response
    {
        $colonnes = $manager->getclassMetadata(User::class)->getFieldNames();

        dump($colonnes);

        $users = $repo->findAll();  // SELECT * FROM user + FETCH_ALL (retour des array multidimentionnel)

        dump($users);

        return $this->render('admin/admin_users.html.twig', [
            'colonnes' => $colonnes,
            'users' => $users
            ]); 
    }

    
    /**
     * @Route("/admin/{id}/edit_user", name="admin_edit_user")
     */
    public function editUser(User $user, Request $request, EntityManagerInterface $manager): Response
    {
        dump($user);

        $formUser = $this->createForm(AdminRegistrationType::class,$user);

        $formUser->handleRequest($request);

        if($formUser->isSubmitted() && $formUser->isValid())
        {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/edit_user.html.twig', [
            'formUser' => $formUser->createView()
        ]);
    }

    
    /**
     * @Route("admin/{id}/delete-user", name="admin_delete_user")
     */
    public function adminDeleteUser(User $user, EntityManagerInterface $manager)
    {
        $manager->remove($user);   // Préparation de la requête DELETE
        $manager->flush();   // execution de la requete DELETE

        // Message de validation de suppression
        $this->addFlash('success', "L'utilisateur a bien été supprimé");

        // redirection vers l'affichage des utilisateurs dans le BACKOFFICE
        return $this->redirectToRoute('admin_users');
    }


}
