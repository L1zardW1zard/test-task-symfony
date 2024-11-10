<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user', uniqueConstraints: [new ORM\UniqueConstraint(columns: ['login', 'pass'])])]

#[ApiResource]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 8, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 1,
        max: 8,
        maxMessage: 'Login must be at most {{ limit }} characters long'
    )]
    private ?string $login = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank]
    #[Assert\Length(
        max: 20,
        maxMessage: 'Phone number must be at most {{ limit }} characters long'
    )]
    #[Assert\Regex(
        pattern: "/^\+?\d{7,15}$/",
        message: 'Phone number is invalid. It must be between 7 and 15 digits, optionally prefixed with +'
    )]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 8)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 2,
        max: 8,
        maxMessage: 'Password must be at most {{ limit }} characters long'
    )]
    private ?string $pass = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    public function __construct()
    {
        $this->roles = [];
    }

    public function getUserIdentifier(): string
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function setPass(string $pass): self
    {
        $this->pass = $pass;
        return $this;
    }

    public function getRoles(): array
    {
        // Ensure every user at least has ROLE_USER
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles ? $roles : [];
        return $this;
    }

    // UserInterface methods

    public function getPassword(): string
    {
        return $this->pass;
    }

    public function getUsername(): string
    {
        return $this->login;
    }

    public function eraseCredentials(): void
    {
        // Clear sensitive data if stored temporarily
    }

    public function getSalt(): ?string
    {
        // Not needed for bcrypt
        return null;
    }
}
