<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'user:read']),
        new GetCollection(normalizationContext: ['groups' => 'user:read'])
    ],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2, max:180, minMessage: 'Too short')]
    #[Groups(['user:read'])]
    private ?string $username = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank()]
    #[Groups(['user:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank()]
    #[Groups(['user:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Assert\NotBlank()]
    #[Assert\Length(min:10,max:15)]
    #[Groups(['user:read'])]
    private ?string $phone = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2, max:100, minMessage: 'Too short')]
    #[Groups(['user:read'])]
    private ?string $email = null;

    #[ORM\Column(options:['default'=>false])]
    #[Groups(['user:read'])]
    private ?bool $administrator = null;

    #[ORM\Column(options:['default'=>true])]
    #[Groups(['user:read'])]
    private ?bool $active = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:read'])]
    private ?Site $site = null;

    #[ORM\OneToMany(mappedBy: 'organiser', targetEntity: Event::class)]
    #[Groups(['user:read'])]
    private Collection $eventsAsOrganiser;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants')]
    #[Groups(['user:read'])]
    private Collection $eventsAsParticipant;

    /**
     * @param int|null $id
     */
    public function __construct()
    {
        $this->administrator = false;
        $this->active = true;
        $this->eventsAsOrganiser = new ArrayCollection();
        $this->eventsAsParticipant = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isAdministrator(): ?bool
    {
        return $this->administrator;
    }

    public function setAdministrator(bool $administrator): static
    {
        $this->administrator = $administrator;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEventsAsOrganiser(): Collection
    {

        //todo fix sort events list by date
//        usort($this->eventsAsOrganiser, function($e1,$e2){
//            return $this->cmpDate($e1->getStartDateTime(), $e2->getStartDateTime());
//        });

        return $this->eventsAsOrganiser;
    }
    private function cmpDate($d1, $d2) {
        if ($d1===$d2) {
            return 0;
        } elseif($d1<$d2) {
            return -1;
        } else {
            return 1;
        }
    }
    public function addEventAsOrganiser(Event $event): static
    {
        if (!$this->eventsAsOrganiser->contains($event)) {
            $this->eventsAsOrganiser->add($event);
            $event->setOrganiser($this);
        }

        return $this;
    }

    public function removeEventAsOrganiser(Event $event): static
    {
        if ($this->eventsAsOrganiser->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getOrganiser() === $this) {
                $event->setOrganiser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEventsAsParticipant(): Collection
    {
        return $this->eventsAsParticipant;
    }

    public function addEventAsParticipant(Event $eventsAsParticipant): static
    {
        if (!$this->eventsAsParticipant->contains($eventsAsParticipant)) {
            $this->eventsAsParticipant->add($eventsAsParticipant);
            $eventsAsParticipant->addParticipant($this);
        }

        return $this;
    }

    public function removeEventAsParticipant(Event $eventsAsParticipant): static
    {
        if ($this->eventsAsParticipant->removeElement($eventsAsParticipant)) {
            $eventsAsParticipant->removeParticipant($this);
        }

        return $this;
    }

    public function fullname(): string {
        return strtoupper($this->getLastName()) . ' '.ucfirst(strtolower($this->getFirstName()));
    }
}
