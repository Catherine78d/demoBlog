<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(): Response
    {
        $user = new User;

        $formRegistration = $this->createForm(RegistrationType::class, $user);

        return $this->render('security/registration.html.twig', [
                'formRegistration' => $formRegistration->createView() 
        ]);
    }
}
