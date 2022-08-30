<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;
use Stringable;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'app_users')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, Stringable, PasswordAuthenticatedUserInterface, EquatableInterface
{
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 25, unique: true)]
    private ?string $username = null;

    #[ORM\Column(type: Types::STRING, length: 254, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    private ?string $plainPassword = null;

    #[ORM\Column(type: Types::STRING, length: 64)]
    private ?string $password = null;

    #[ORM\Column(type: Types::STRING, length: 250)]
    private ?string $salt = null;

    #[ORM\Column(name: 'is_active', type: Types::BOOLEAN)]
    private ?bool $isActive = null;

    /**
     * @var string[]
     */
    #[ORM\Column(name: 'roles', type: 'array')]
    private array $roles = [];

    public function __construct()
    {
        $this->isActive = false;
        $this->salt = md5(uniqid('', true));
    }

    public function __toString(): string
    {
        return $this->getUsername();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;
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

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        if (empty($this->roles)) {
            return ['ROLE_USER'];
        }
        return $this->roles;
    }

    /**
     * @param string[] $roles
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    function addRole(string $role)
    {
        $this->roles[] = $role;
    }

    function getIsActive(): bool
    {
        return $this->isActive;
    }

    function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['id' => "int", 'username' => "string"])]
    public function __serialize(): array
    {
        return [
            'id'       => $this->id,
            'username' => $this->username,
        ];
    }

    public function __unserialize(array $data)
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $this->id === $user->getId();
    }
}