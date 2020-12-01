<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, AuthorizationCheckerInterface $authChecker): Response
    {
        // Si l'internaute est connecté, il n'a rien à faire sur la route '/connexion', on le redirige vers la route '/blog'
        if ($authChecker->isGranted('ROLE_USER')) 
        {
            return $this->redirectToRoute(('blog'));
        }
        
        $user = new User;

        $formRegistration = $this->createForm(RegistrationType::class, $user);

        $formRegistration->handleRequest($request);

        if($formRegistration->isSubmitted() && $formRegistration->isValid())
        {
            // encodePassword() est une méthode issue de l'interface UserPasswordEncoderInterface permettant d'encoder le mot de passe
            // Pour encoder le mot de passe, il faut que notre User implémente l'interface UserInterface et nous devons déclarer obligatoirement 5 méthodes dans l'entité :
            //  -> getPassword(), getUsername(); getSalt(); getRoles() et eraseCredentials()
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);   // on envoie le mot de passe haché dans l'entité $user

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('security_login');


        }

        return $this->render('security/registration.html.twig', [
                'formRegistration' => $formRegistration->createView() 
        ]);
    }


    /**
     * @Route("/connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, AuthorizationCheckerInterface $authChecker): Response
    {
        // Si l'internaute est connecté, il n'a rien à faire sur la route '/connexion', on le redirige vers la route '/blog'
        if ($authChecker->isGranted('ROLE_USER')) 
        {
            return $this->redirectToRoute(('blog'));
        }

        // Récupéreation du message d'erreur en cas de mauvaise connexion
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupértation du dernier username (email) saisi par l'internaute en cas d'erreur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername
        ]);
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
        // Cette méthode ne retourne rien, il nous suffit d'avoir une route pour se deconnecter
    }


}
