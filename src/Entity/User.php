<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(
 *      fields = {"email"},
 *      message="Un compte est déjà existant à cette adresse Email"
 * )
 *  @UniqueEntity(
 *      fields = {"username"},
 *      message="Ce nom d'utilisateur est déjà existant à cette adresse Email"
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez renseigner un Email")
     * @Assert\Email(message="Veuillez saisir une adresse Email valide")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min="2",
     *      minMessage="Votre mon d'utilisateur doit contenir minimum 2 caratères"
     * )
     * @Assert\NotBlank(message="Veuillez renseigner un nom d'utilisateur")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min="8",
     *      minMessage="Votre mot de passe doit faire minimum 8 caratères"
     * )
     * @Assert\EqualTo(
     *      propertyPath="confirm_password",
     *      message="Les mots de passe ne correspondent pas"
     * )
     */
    private $password;

    /**
    * @Assert\EqualTo(
    *      propertyPath="password",
    *      message="Les mots de passe ne correspondent pas"
    * )
    */
    public $confirm_password;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
    
    // cette méthode est uniquement destinée à nettoyer les mots de passe en texte brut éventuellement stockés
    public function eraseCredentials()
    {
    
    }
    
    // Renvoie la chaîne de caractères non encodée que l'utilisateur a saisi, qui est utilisé à l'origine pour encoder le mot de passe
    public function getSalt()
    {
    
    }
    
    // cette fonction renvoie un tableau de chaîne de caractères
    // Renvoie les rôles accordés à l'utilisateur
    public function getRoles()
    {
        // return ["ROLE_User"];
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}