<?php

namespace App\Entity\Account;

use App\Entity\Post;
use App\Entity\Task;
use App\Entity\Trait\EntityIdTrait;
use App\Entity\Trait\EntityTimestampTrait;
use App\Enum\Roles;
use App\Enum\UserType;
use App\Repository\Account\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    UserType::ADMIN->value => Admin::class,
    UserType::CUSTOMER->value => Customer::class,
])]
#[ORM\Table(name: '`users`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use EntityIdTrait;
    use EntityTimestampTrait;

    const PASSWORD_PATTERN = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/';
    const PASSWORD_MESSAGE = 'The password must contain at least one lowercase letter, one uppercase letter, one digit and one special character.';

    #[ORM\Column(length: 50, nullable: true)]
    protected ?string $username = null;

    #[ORM\Column(length: 180, nullable: true)]
    protected ?string $email = null;

    #[ORM\Column]
    protected array $roles;

    #[ORM\Column(nullable: true)]
    protected ?string $password = null;

    #[SerializedName('password')]
    protected ?string $plainPassword = null;

    #[SerializedName('confirmPassword')]
    protected ?string $confirmPlainPassword = null;

    #[ORM\Column(length: 50, nullable: true)]
    protected ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    protected ?string $lastName = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $resetPasswordToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $resetPasswordTokenExpiry = null;

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'author')]
    private Collection $posts;

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'user')]
    private Collection $tasks;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }


    public function getFullName(): string
    {
        return "$this->firstName $this->lastName";
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email ?: null;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username ?? $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = Roles::ROLE_ACL_DEFAULT->value;

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
        $this->confirmPlainPassword = null;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getConfirmPlainPassword(): ?string
    {
        return $this->confirmPlainPassword;
    }

    public function setConfirmPlainPassword(?string $confirmPlainPassword): static
    {
        $this->confirmPlainPassword = $confirmPlainPassword;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function hasRole(string $string): bool
    {
        return in_array($string, $this->roles);
    }

    public function setResetPasswordToken(?string $token): self
    {
        $this->resetPasswordToken = $token;
        return $this;
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordTokenExpiry(?\DateTime $expiry): self
    {
        $this->resetPasswordTokenExpiry = $expiry;
        return $this;
    }

    public function getResetPasswordTokenExpiry(): ?\DateTime
    {
        return $this->resetPasswordTokenExpiry;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }
        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }
        return $this;
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setUser($this);
        }
        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }
        return $this;
    }

}
