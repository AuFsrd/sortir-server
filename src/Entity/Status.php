<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get()
    ],
    security: "is_granted('ROLE_ADMIN')"
)]
#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    final const STATES = [
        'CREATED',
        'OPEN',
        'CLOSED',
        'IN PROGRESS',
        'PAST',
        'CANCELLED',
        'ARCHIVED',
        ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event:read', 'event:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2, max:30, minMessage: 'Too short')]
    #[Groups(['event:read', 'event:write'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Event::class)]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setStatus($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getStatus() === $this) {
                $event->setStatus(null);
            }
        }

        return $this;
    }
}
